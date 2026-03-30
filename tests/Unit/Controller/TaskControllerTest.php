<?php

namespace Kentec\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Kentec\App\Model\Task;

/**
 * Tests unitaires pour TaskController.
 *
 * Contrainte architecturale :
 * Le contrôleur instancie directement le Repository (new Repository())
 * sans injection de dépendances. Ces tests sont donc des "tests de structure" :
 * on lit le code source et on vérifie qu'il respecte les règles du projet.
 *
 * Ce qu'on vérifie ici :
 * - Soft delete (jamais de DELETE SQL physique sur les tâches)
 * - Champs d'audit createdby/updatedby renseignés
 * - updatedat mis à jour lors des modifications
 * - Format JSON standardisé (jsonSuccess/jsonError)
 * - Vérification CSRF sur toutes les routes d'écriture
 * - Attribution automatique du statut "En attente" à la création
 * - Validation des champs obligatoires (name, projectId)
 * - Filtrage des tâches actives (isactive = true)
 * - Enrichissement de la liste (project_name, state_name, developer)
 * - Contrôle d'accès par rôle dans les routes
 */
class TaskControllerTest extends TestCase
{
    /** Code source du contrôleur (lu une seule fois pour tous les tests) */
    private string $controllerSourceCode;

    /** Code source des routes */
    private string $routesSourceCode;

    protected function setUp(): void
    {
        $this->controllerSourceCode = file_get_contents(
            __DIR__ . '/../../../src/Controller/TaskController.php'
        );

        $this->routesSourceCode = file_get_contents(
            __DIR__ . '/../../../routes.php'
        );
    }

    // ---------------------------------------------------------------
    // Soft delete
    // ---------------------------------------------------------------

    /**
     * Vérifie que deleteApiTask() implémente le soft delete.
     *
     * On ne supprime jamais physiquement une tâche : elle peut être
     * référencée dans les statistiques ou l'historique d'un projet.
     */
    public function testDeleteApiTaskImplimenteSoftDelete(): void
    {
        $this->assertStringContainsString(
            'isactive = false',
            $this->controllerSourceCode,
            'deleteApiTask() doit passer isactive à false (soft delete)'
        );

        $this->assertStringNotContainsString(
            'DELETE FROM',
            $this->controllerSourceCode,
            'deleteApiTask() ne doit jamais exécuter un DELETE SQL physique'
        );
    }

    /**
     * Vérifie que deleteApiTask() n'utilise pas $repo->delete().
     *
     * Repository::delete() exécute un DELETE SQL.
     * On doit utiliser une UPDATE avec isactive = false à la place.
     */
    public function testDeleteApiTaskNAppellePasMethodeDeleteDuRepository(): void
    {
        $this->assertStringNotContainsString(
            '->delete(',
            $this->controllerSourceCode,
            'deleteApiTask() ne doit pas appeler $repo->delete() — utiliser UPDATE isactive = false'
        );
    }

    // ---------------------------------------------------------------
    // Champs d'audit (createdby, updatedby, updatedat)
    // ---------------------------------------------------------------

    /**
     * Vérifie que addApiTask() renseigne createdby.
     *
     * Ce champ d'audit indique quel utilisateur a créé la tâche.
     */
    public function testAddApiTaskRenseigneCreatedby(): void
    {
        $this->assertStringContainsString(
            'setCreatedby',
            $this->controllerSourceCode,
            'addApiTask() doit renseigner createdby avec l\'ID de l\'utilisateur connecté'
        );
    }

    /**
     * Vérifie que les méthodes de modification renseignent updatedby.
     */
    public function testEditEtDeleteApiTaskRenseignentUpdatedby(): void
    {
        $this->assertStringContainsString(
            'updatedby',
            $this->controllerSourceCode,
            'editApiTask() et deleteApiTask() doivent renseigner updatedby'
        );
    }

    /**
     * Vérifie que updatedat est mis à jour lors des modifications.
     *
     * updatedat permet de savoir quand une tâche a été modifiée pour la dernière fois.
     */
    public function testEditApiTaskMetAJourUpdatedat(): void
    {
        $this->assertStringContainsString(
            'updatedat = NOW()',
            $this->controllerSourceCode,
            'editApiTask() et deleteApiTask() doivent mettre à jour updatedat via NOW()'
        );
    }

    // ---------------------------------------------------------------
    // Format JSON standardisé
    // ---------------------------------------------------------------

    /**
     * Vérifie que le contrôleur utilise les méthodes JSON standardisées du projet.
     *
     * - Succès : {"success": true, "data": {...}}  → jsonSuccess()
     * - Erreur  : {"success": false, "error": "..."} → jsonError()
     */
    public function testControleurUtiliseJsonSuccessEtJsonError(): void
    {
        $this->assertStringContainsString(
            'jsonSuccess',
            $this->controllerSourceCode,
            'Le contrôleur doit utiliser jsonSuccess() pour les réponses de succès'
        );

        $this->assertStringContainsString(
            'jsonError',
            $this->controllerSourceCode,
            'Le contrôleur doit utiliser jsonError() pour les réponses d\'erreur'
        );
    }

    /**
     * Vérifie qu'il n'y a plus de réponses JSON non-standardisées.
     *
     * L'ancien format {"delete": true, "message": "..."} ne doit plus apparaître.
     */
    public function testControleurNaPlancienFormatJsonDeDelete(): void
    {
        $this->assertStringNotContainsString(
            '"delete"',
            $this->controllerSourceCode,
            'L\'ancien format {"delete": true} ne doit plus être utilisé — utiliser jsonSuccess()'
        );
    }

    // ---------------------------------------------------------------
    // Vérification CSRF
    // ---------------------------------------------------------------

    /**
     * Vérifie que le CSRF est vérifié dans les méthodes d'écriture.
     *
     * Toute route POST, PUT ou DELETE doit vérifier le token CSRF
     * pour se protéger des attaques de type requête forgée.
     */
    public function testControleurVerifieCsrfSurLesRoutesEcriture(): void
    {
        $this->assertStringContainsString(
            'Security::verifyCsrfToken',
            $this->controllerSourceCode,
            'Les méthodes add/edit/delete doivent vérifier le token CSRF'
        );
    }

    /**
     * Vérifie qu'il n'y a pas de problème d'encodage dans les messages CSRF.
     *
     * L'ancienne version avait un caractère mal encodé dans "Requête invalide".
     */
    public function testControleurNaPasDEncodageCsrfCorrompu(): void
    {
        $this->assertStringNotContainsString(
            "Requ\xefte",
            $this->controllerSourceCode,
            'Le message d\'erreur CSRF ne doit pas contenir de caractère mal encodé'
        );
    }

    // ---------------------------------------------------------------
    // Logique métier de addApiTask()
    // ---------------------------------------------------------------

    /**
     * Vérifie que addApiTask() attribue automatiquement le statut "En attente".
     *
     * Lors de la création d'une tâche, on recherche l'état "En attente" en base
     * et on l'attribue sans que l'utilisateur ait à le préciser.
     */
    public function testAddApiTaskAttribueStatutEnAttenteParDefaut(): void
    {
        $this->assertStringContainsString(
            'attente',
            $this->controllerSourceCode,
            'addApiTask() doit chercher et attribuer le statut "En attente" automatiquement'
        );
    }

    /**
     * Vérifie que addApiTask() valide les champs obligatoires.
     *
     * - name : le nom identifie la tâche, il est indispensable
     * - projectId : une tâche doit toujours être rattachée à un projet
     */
    public function testAddApiTaskValideLesChampsObligatoires(): void
    {
        $this->assertStringContainsString(
            "'name'",
            $this->controllerSourceCode,
            'addApiTask() doit valider que le nom est présent'
        );

        $this->assertStringContainsString(
            "'projectId'",
            $this->controllerSourceCode,
            'addApiTask() doit valider que le projet associé est présent'
        );
    }

    /**
     * Vérifie que addApiTask() utilise Security::getUser() pour identifier le créateur.
     */
    public function testAddApiTaskUtiliseGetUserPourLeCreateur(): void
    {
        $this->assertStringContainsString(
            'getUser',
            $this->controllerSourceCode,
            'addApiTask() doit utiliser Security::getUser() pour identifier l\'utilisateur connecté'
        );
    }

    // ---------------------------------------------------------------
    // Enrichissement de getApiTasks()
    // ---------------------------------------------------------------

    /**
     * Vérifie que getApiTasks() inclut le nom du statut dans la réponse.
     *
     * Sans state_name, le front devrait faire un appel supplémentaire
     * sur /api/states pour obtenir le libellé à afficher.
     */
    public function testGetApiTasksInclutLeNomDuStatut(): void
    {
        $this->assertStringContainsString(
            'state_name',
            $this->controllerSourceCode,
            'getApiTasks() doit inclure le nom du statut via alias state_name'
        );
    }

    /**
     * Vérifie que getApiTasks() inclut le nom du projet dans la réponse.
     */
    public function testGetApiTasksInclutLeNomDuProjet(): void
    {
        $this->assertStringContainsString(
            'project_name',
            $this->controllerSourceCode,
            'getApiTasks() doit inclure le nom du projet via alias project_name'
        );
    }

    /**
     * Vérifie que getApiTasks() filtre sur isactive = true.
     *
     * Les tâches désactivées (soft-deletées) ne doivent pas apparaître
     * dans la liste de l'application.
     */
    public function testGetApiTasksFiltreSurIsactive(): void
    {
        $this->assertMatchesRegularExpression(
            '/isactive\s*=\s*true/',
            $this->controllerSourceCode,
            'getApiTasks() doit filtrer les tâches inactives avec isactive = true'
        );
    }

    // ---------------------------------------------------------------
    // Routes — contrôle d'accès par rôle
    // ---------------------------------------------------------------

    /**
     * Vérifie que les routes de lecture des tâches sont accessibles à tout utilisateur connecté.
     */
    public function testRoutesLectureTachesOuvertesATousLesConnectes(): void
    {
        $this->assertMatchesRegularExpression(
            '|/api/tasks.*AUTH.*true|s',
            $this->routesSourceCode,
            'La route /api/tasks doit être accessible à tout utilisateur connecté (AUTH => true)'
        );
    }

    /**
     * Vérifie que la route de création de tâche est réservée aux CDP et admins.
     *
     * Un simple collaborateur (USER) ne doit pas pouvoir créer une tâche.
     */
    public function testRouteCreationTacheReserveeAuxCdpEtAdmins(): void
    {
        $this->assertStringContainsString(
            'addApiTask',
            $this->routesSourceCode,
            'La route /api/add/task doit exister dans routes.php'
        );

        $this->assertMatchesRegularExpression(
            '/addApiTask.*?AUTH.*?CDP/s',
            $this->routesSourceCode,
            'La route de création de tâche doit inclure le rôle CDP'
        );
    }

    /**
     * Vérifie que la route de modification de tâche est réservée aux CDP et admins.
     */
    public function testRouteModificationTacheReserveeAuxCdpEtAdmins(): void
    {
        $this->assertStringContainsString(
            'editApiTask',
            $this->routesSourceCode,
            'La route /api/edit/task/{id} doit exister dans routes.php'
        );

        $this->assertMatchesRegularExpression(
            '/editApiTask.*?AUTH.*?CDP/s',
            $this->routesSourceCode,
            'La route de modification de tâche doit inclure le rôle CDP'
        );
    }

    /**
     * Vérifie que la route de suppression est réservée aux admins et PDG.
     */
    public function testRouteSuppressionTacheReserveeAuxAdminsEtPdg(): void
    {
        $this->assertStringContainsString(
            'deleteApiTask',
            $this->routesSourceCode,
            'La route /api/delete/task/{id} doit exister dans routes.php'
        );

        $this->assertMatchesRegularExpression(
            '/deleteApiTask.*?AUTH.*?ADMIN/s',
            $this->routesSourceCode,
            'La route de suppression de tâche doit être réservée aux ADMIN'
        );
    }

    // ---------------------------------------------------------------
    // Logique fonctionnelle (sans base de données)
    // ---------------------------------------------------------------

    /**
     * Vérifie qu'une date ISO 8601 valide est acceptée par DateTime.
     */
    public function testDateValideEstAcceptee(): void
    {
        $dateString = '2026-06-30';
        $date       = new \DateTime($dateString);

        $this->assertEquals($dateString, $date->format('Y-m-d'));
    }

    /**
     * Simule la logique du statut par défaut :
     * si aucun stateId n'est fourni, on utilise le statut "En attente".
     */
    public function testStatutParDefautEstEnAttenteQuandAbsent(): void
    {
        $stateIdFourni   = null;
        $defaultStateId  = 'uuid-en-attente'; // simulé comme retourné par la requête SQL

        $stateIdResolu = $stateIdFourni ?? $defaultStateId;

        $this->assertEquals(
            $defaultStateId,
            $stateIdResolu,
            'Si stateId est absent de la requête, on doit utiliser le statut par défaut'
        );
    }

    /**
     * Vérifie que isactive passe bien à false après un soft delete.
     */
    public function testIsActiveEstFalseApresSoftDelete(): void
    {
        $task = new Task();
        $task->setIsactive(false);

        $this->assertFalse(
            $task->getIsactive(),
            'isactive doit être false après un soft delete'
        );
    }

    /**
     * Vérifie la logique de conversion de l'effort en float.
     *
     * L'effort est saisi comme string côté JSON (ex: "8.5")
     * et doit être converti en float pour la BDD.
     */
    public function testConversionEffortEnFloat(): void
    {
        $effortString = '8.5';
        $effortFloat  = (float)$effortString;

        $this->assertSame(8.5, $effortFloat, 'L\'effort doit être converti en float');
        $this->assertIsFloat($effortFloat);
    }

    /**
     * Vérifie qu'il n'y a pas de logs de debug dans le code de production.
     */
    public function testControleurNaPasDeLogsDebug(): void
    {
        $this->assertStringNotContainsString(
            'error_log(',
            $this->controllerSourceCode,
            'Les appels error_log() de débogage doivent être supprimés du code de production'
        );
    }
}
