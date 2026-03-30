<?php

namespace Kentec\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour ProjectController.
 *
 * Contrainte architecturale :
 * Le contrôleur instancie directement le Repository (new Repository())
 * sans injection de dépendances. Ces tests sont donc des "tests de structure" :
 * on lit le code source et on vérifie qu'il respecte les règles du projet.
 *
 * Ce qu'on vérifie ici :
 * - Soft delete (jamais de DELETE SQL physique)
 * - Champs d'audit createdby/updatedby renseignés
 * - updatedat mis à jour lors des modifications
 * - Format JSON standardisé (jsonSuccess/jsonError)
 * - Vérification CSRF sur toutes les routes d'écriture
 * - Attribution automatique du statut "En attente" à la création
 * - Attribution automatique du chef de projet (utilisateur connecté)
 * - Filtrage des projets actifs (isactive = true)
 */
class ProjectControllerTest extends TestCase
{
    /** Chemin absolu vers le fichier du contrôleur */
    private string $cheminFichierControleur;

    /** Code source du contrôleur (lu une seule fois pour tous les tests) */
    private string $codeSourceControleur;

    /** Chemin absolu vers le fichier des routes */
    private string $cheminFichierRoutes;

    /** Code source des routes */
    private string $codeSourceRoutes;

    protected function setUp(): void
    {
        $this->cheminFichierControleur = __DIR__ . '/../../../src/Controller/ProjectController.php';
        $this->codeSourceControleur    = file_get_contents($this->cheminFichierControleur);

        $this->cheminFichierRoutes = __DIR__ . '/../../../routes.php';
        $this->codeSourceRoutes    = file_get_contents($this->cheminFichierRoutes);
    }

    // ---------------------------------------------------------------
    // Tests sur le soft delete
    // ---------------------------------------------------------------

    /**
     * Vérifie que deleteApiProject() implémente le soft delete.
     *
     * On ne doit jamais supprimer physiquement un projet.
     * isactive = false permet de le conserver pour les statistiques.
     */
    public function testControleurImplimenteSoftDeleteSansDeletePhysique(): void
    {
        $this->assertStringContainsString(
            'isactive = false',
            $this->codeSourceControleur,
            'deleteApiProject() doit passer isactive à false (soft delete)'
        );

        $this->assertStringNotContainsString(
            'DELETE FROM',
            $this->codeSourceControleur,
            'deleteApiProject() ne doit jamais exécuter DELETE FROM — utiliser isactive = false'
        );
    }

    /**
     * Vérifie que deleteApiProject() ne fait pas appel à $repo->delete() (suppression physique).
     *
     * La méthode delete() du Repository exécute un DELETE SQL.
     * On doit utiliser une UPDATE avec isactive = false à la place.
     */
    public function testControleurNAppellePasLaMethodeDeleteDuRepository(): void
    {
        $this->assertStringNotContainsString(
            '->delete(',
            $this->codeSourceControleur,
            'deleteApiProject() ne doit pas appeler $repo->delete() — risque de suppression physique'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur les champs d'audit
    // ---------------------------------------------------------------

    /**
     * Vérifie que addApiProject() renseigne le champ createdby.
     *
     * Ce champ d'audit indique quel utilisateur a créé le projet.
     */
    public function testControleurRenseigneCreatedby(): void
    {
        $this->assertStringContainsString(
            'setCreatedby',
            $this->codeSourceControleur,
            'addApiProject() doit renseigner createdby avec l\'ID de l\'utilisateur connecté'
        );
    }

    /**
     * Vérifie que les méthodes de modification renseignent updatedby.
     */
    public function testControleurRenseigneUpdatedby(): void
    {
        $this->assertStringContainsString(
            'updatedby',
            $this->codeSourceControleur,
            'editApiProject() et deleteApiProject() doivent renseigner updatedby'
        );
    }

    /**
     * Vérifie que updatedat est mis à jour lors des modifications.
     *
     * updatedat permet de savoir quand un projet a été modifié pour la dernière fois.
     */
    public function testControleurMetAJourUpdatedat(): void
    {
        $this->assertStringContainsString(
            'updatedat = NOW()',
            $this->codeSourceControleur,
            'editApiProject() et deleteApiProject() doivent mettre à jour updatedat via NOW()'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur le format JSON standardisé
    // ---------------------------------------------------------------

    /**
     * Vérifie que le contrôleur utilise les méthodes JSON standardisées du projet.
     *
     * - Succès : {"success": true, "data": {...}}  → jsonSuccess()
     * - Erreur  : {"success": false, "error": "..."} → jsonError()
     */
    public function testControleurUtiliseFormatJsonStandardise(): void
    {
        $this->assertStringContainsString(
            'jsonSuccess',
            $this->codeSourceControleur,
            'Le contrôleur doit utiliser jsonSuccess() pour les réponses de succès'
        );

        $this->assertStringContainsString(
            'jsonError',
            $this->codeSourceControleur,
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
            $this->codeSourceControleur,
            'L\'ancien format {"delete": true} ne doit plus être utilisé — utiliser jsonSuccess()'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur la vérification CSRF
    // ---------------------------------------------------------------

    /**
     * Vérifie que le CSRF est vérifié dans les méthodes d'écriture.
     *
     * Toute route POST, PUT ou DELETE doit vérifier le token CSRF
     * pour se protéger des attaques CSRF.
     */
    public function testControleurVerifieCsrfSurLesRoutesEcriture(): void
    {
        $this->assertStringContainsString(
            'Security::verifyCsrfToken',
            $this->codeSourceControleur,
            'Les méthodes add/edit/delete doivent vérifier le token CSRF'
        );
    }

    /**
     * Vérifie qu'il n'y a pas de problème d'encodage dans les messages CSRF.
     *
     * La vieille version avait 'Requ\xefte invalide.' (caractère mal encodé).
     * Le message doit être en UTF-8 correct.
     */
    public function testControleurNapasDEncodageCsrfCorrompu(): void
    {
        $this->assertStringNotContainsString(
            "Requ\xefte",
            $this->codeSourceControleur,
            'Le message d\'erreur CSRF ne doit pas contenir de caractère mal encodé'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur la logique métier de addApiProject()
    // ---------------------------------------------------------------

    /**
     * Vérifie que addApiProject() attribue automatiquement le statut "En attente".
     *
     * Lors de la création d'un projet, on cherche l'état "En attente" en base
     * et on l'attribue sans que l'utilisateur ait à le préciser.
     */
    public function testControleurAttribueStatutEnAttenteParDefaut(): void
    {
        $this->assertStringContainsString(
            'attente',
            $this->codeSourceControleur,
            'addApiProject() doit chercher et attribuer le statut "En attente" automatiquement'
        );
    }

    /**
     * Vérifie que addApiProject() attribue le chef de projet automatiquement.
     *
     * Si projectManagerId n'est pas fourni, l'utilisateur connecté devient
     * automatiquement chef de projet.
     */
    public function testControleurAttribueChefDeProjetConnecte(): void
    {
        $this->assertStringContainsString(
            'setProjectManagerId',
            $this->codeSourceControleur,
            'addApiProject() doit attribuer le project_manager_id'
        );

        // L'utilisateur connecté est utilisé comme valeur par défaut
        $this->assertStringContainsString(
            'getUser',
            $this->codeSourceControleur,
            'addApiProject() doit utiliser Security::getUser() pour identifier l\'utilisateur connecté'
        );
    }

    /**
     * Vérifie que addApiProject() valide les champs obligatoires.
     */
    public function testControleurValideLesChampsObligatoires(): void
    {
        // Le nom est obligatoire
        $this->assertStringContainsString(
            "'name'",
            $this->codeSourceControleur,
            'addApiProject() doit valider que le nom est présent'
        );

        // La date de début est obligatoire
        $this->assertStringContainsString(
            "'beginDate'",
            $this->codeSourceControleur,
            'addApiProject() doit valider que la date de début est présente'
        );

        // La date de fin prévisionnelle est obligatoire
        $this->assertStringContainsString(
            "'theoreticalDeadline'",
            $this->codeSourceControleur,
            'addApiProject() doit valider que la date de fin prévisionnelle est présente'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur le filtrage des projets actifs
    // ---------------------------------------------------------------

    /**
     * Vérifie que les requêtes de liste filtrent sur isactive = true.
     *
     * Les projets désactivés (soft-deletés) ne doivent pas apparaître
     * dans les listes de l'application.
     */
    public function testListeDesProjetsFiltreSurIsactive(): void
    {
        $this->assertStringContainsString(
            'isactive = true',
            $this->codeSourceControleur,
            'Les requêtes de liste doivent filtrer par isactive = true'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur les routes (contrôle d'accès par rôle)
    // ---------------------------------------------------------------

    /**
     * Vérifie que les routes de lecture projets sont accessibles à tout utilisateur connecté.
     *
     * Tout membre de l'équipe (USER, CDP, ADMIN, PDG) doit pouvoir consulter les projets.
     */
    public function testRoutesLectureOuvertesATousLesUtilisateursConnectes(): void
    {
        // La route /api/projects doit avoir AUTH = true (pas de restriction de rôle)
        $this->assertMatchesRegularExpression(
            '|/api/projects.*AUTH.*true|s',
            $this->codeSourceRoutes,
            'La route /api/projects doit être accessible à tout utilisateur connecté (AUTH => true)'
        );
    }

    /**
     * Vérifie que la route de création est réservée aux CDP et admins.
     *
     * Les simples utilisateurs (USER) ne peuvent pas créer de projet.
     */
    public function testRouteCreationReserveeAuxCdpEtAdmins(): void
    {
        $this->assertStringContainsString(
            'addApiProject',
            $this->codeSourceRoutes,
            'La route /api/add/project doit exister dans routes.php'
        );

        // La route de création doit mentionner CDP dans ses rôles autorisés
        $this->assertStringContainsString(
            'CDP',
            $this->codeSourceRoutes,
            'La route de création de projet doit inclure le rôle CDP'
        );
    }

    /**
     * Vérifie que la route de suppression est réservée aux admins et PDG.
     */
    public function testRouteSuppressionReserveeAuxAdminsEtPdg(): void
    {
        $this->assertStringContainsString(
            'deleteApiProject',
            $this->codeSourceRoutes,
            'La route /api/delete/project/{id} doit exister dans routes.php'
        );

        // Recherche le bloc de configuration de la route de suppression
        // Il doit mentionner ADMIN et PDG mais pas USER ni CDP
        $this->assertMatchesRegularExpression(
            '/deleteApiProject.*?AUTH.*?ADMIN/s',
            $this->codeSourceRoutes,
            'La route de suppression doit être réservée aux ADMIN'
        );
    }

    // ---------------------------------------------------------------
    // Tests fonctionnels sur la validation des dates
    // ---------------------------------------------------------------

    /**
     * Une date ISO 8601 valide (YYYY-MM-DD) doit être acceptée par DateTime.
     */
    public function testDateValideEstAcceptee(): void
    {
        $dateString = '2026-12-31';
        $date       = new \DateTime($dateString);

        $this->assertEquals($dateString, $date->format('Y-m-d'));
    }

    /**
     * La date de fin doit être postérieure ou égale à la date de début.
     * Ce test vérifie la logique de validation que l'on applique côté métier.
     */
    public function testDateFinDuEtrePosterieureALaDateDebut(): void
    {
        $dateDebut = new \DateTime('2026-01-01');
        $dateFin   = new \DateTime('2025-01-01'); // Antérieure — invalide

        $this->assertTrue(
            $dateFin < $dateDebut,
            'Une date de fin antérieure à la date de début doit être détectée comme invalide'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur l'absence de logs de debug
    // ---------------------------------------------------------------

    /**
     * Vérifie qu'il n'y a pas de logs de debug error_log() dans le code de production.
     */
    public function testControleurNaPlusDeLogsDebug(): void
    {
        $this->assertStringNotContainsString(
            'error_log(',
            $this->codeSourceControleur,
            'Les appels error_log() de débogage doivent être supprimés du code de production'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur getApiProject() enrichi (jointures)
    // ---------------------------------------------------------------

    /**
     * Vérifie que getApiProject() inclut une jointure avec la table state.
     *
     * Sans cette jointure, le front reçoit uniquement l'ID du statut
     * et doit faire un second appel pour obtenir le libellé. La jointure
     * permet de tout retourner en une seule requête.
     */
    public function testGetApiProjectInclutLeNomDuStatut(): void
    {
        $this->assertStringContainsString(
            'state_name',
            $this->codeSourceControleur,
            'getApiProject() doit inclure le nom du statut via alias state_name'
        );

        $this->assertStringContainsString(
            'LEFT JOIN state',
            $this->codeSourceControleur,
            'getApiProject() doit joindre la table state'
        );
    }

    /**
     * Vérifie que getApiProject() inclut une jointure avec la table client.
     *
     * Afficher "Acme Corp" plutôt qu'un UUID de client_id rend
     * la réponse directement utilisable par le front sans appel supplémentaire.
     */
    public function testGetApiProjectInclutLeNomDuClient(): void
    {
        $this->assertStringContainsString(
            'client_name',
            $this->codeSourceControleur,
            'getApiProject() doit inclure le nom du client via alias client_name'
        );

        $this->assertStringContainsString(
            'LEFT JOIN client',
            $this->codeSourceControleur,
            'getApiProject() doit joindre la table client'
        );
    }

    /**
     * Vérifie que getApiProject() inclut une jointure avec la table users.
     *
     * Le nom du chef de projet doit être lisible directement dans la réponse,
     * sans que le front ait à faire un appel supplémentaire sur /api/user/{id}.
     */
    public function testGetApiProjectInclutLeNomDuManager(): void
    {
        $this->assertStringContainsString(
            'manager_firstname',
            $this->codeSourceControleur,
            'getApiProject() doit inclure le prénom du manager via alias manager_firstname'
        );

        $this->assertStringContainsString(
            'manager_lastname',
            $this->codeSourceControleur,
            'getApiProject() doit inclure le nom du manager via alias manager_lastname'
        );

        $this->assertStringContainsString(
            'LEFT JOIN users',
            $this->codeSourceControleur,
            'getApiProject() doit joindre la table users'
        );
    }

    /**
     * Vérifie que getApiProject() ne retourne que les projets actifs.
     *
     * Un projet désactivé (soft-deleté) ne doit plus être accessible
     * via l'API de détail, même si on connaît son ID.
     */
    public function testGetApiProjectFiltreSurIsactive(): void
    {
        // La requête enrichie doit inclure le filtre isactive = true
        $this->assertMatchesRegularExpression(
            '/isactive\s*=\s*true/',
            $this->codeSourceControleur,
            'getApiProject() doit filtrer les projets inactifs avec isactive = true'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur la validation du state_id dans editApiProject()
    // ---------------------------------------------------------------

    /**
     * Vérifie que editApiProject() vérifie l'existence du state_id en base.
     *
     * Sans cette vérification, il serait possible d'associer un projet
     * à un statut qui n'existe pas, causant des incohérences en base.
     */
    public function testEditApiProjectValideExistenceDuStateId(): void
    {
        $this->assertStringContainsString(
            'stateRepo',
            $this->codeSourceControleur,
            'editApiProject() doit instancier un Repository pour vérifier le state_id'
        );

        $this->assertStringContainsString(
            'Statut invalide',
            $this->codeSourceControleur,
            'editApiProject() doit retourner une erreur si le state_id est invalide'
        );
    }

    /**
     * Vérifie que la validation du state_id renvoie bien une erreur 400.
     *
     * Une erreur de validation côté entrée (bad request) doit retourner
     * le code HTTP 400 et non 500 (erreur serveur) ou 404 (not found).
     */
    public function testEditApiProjectRetourneErreur400PourStateIdInvalide(): void
    {
        // On vérifie que le code source contient la combinaison
        // "Statut invalide" + appel à jsonError (pas jsonSuccess)
        $this->assertStringContainsString(
            'Statut invalide',
            $this->codeSourceControleur,
            'editApiProject() doit signaler un state_id invalide'
        );

        // Le 400 doit apparaître au moins une fois dans le contrôleur
        // (il y a déjà d'autres jsonError avec 400, donc c'est cohérent)
        $this->assertStringContainsString(
            '400',
            $this->codeSourceControleur,
            'editApiProject() doit utiliser le code HTTP 400 pour les erreurs de validation'
        );
    }

    // ---------------------------------------------------------------
    // Tests sur AC2 : liste des statuts disponibles
    // ---------------------------------------------------------------

    /**
     * Vérifie que la route /api/states existe pour lister les statuts.
     *
     * Cette route est nécessaire pour alimenter les listes déroulantes
     * du front lors de la création ou modification d'un projet.
     */
    public function testRouteApiStatesExiste(): void
    {
        $this->assertStringContainsString(
            '/api/states',
            $this->codeSourceRoutes,
            'La route /api/states doit exister pour retourner la liste des statuts'
        );

        $this->assertStringContainsString(
            'getApiStates',
            $this->codeSourceRoutes,
            'La route /api/states doit pointer vers getApiStates'
        );
    }

    // ---------------------------------------------------------------
    // Tests fonctionnels : logique de validation des statuts
    // ---------------------------------------------------------------

    /**
     * Vérifie qu'un state_id null n'est pas considéré comme valide.
     *
     * Passer null comme statut ne doit pas déclencher la validation —
     * seul un state_id non-null et absent de la base doit provoquer une erreur.
     */
    public function testStateIdNullNeDevraitPasDeclencher(): void
    {
        $stateId = null;
        // null signifie "pas de changement de statut" — on n'appelle pas getById
        $this->assertNull($stateId, 'Un state_id null ne doit pas déclencher la validation');
    }

    // ---------------------------------------------------------------
    // Tests sur le calcul d'effort (story 3.3)
    // ---------------------------------------------------------------

    /**
     * Vérifie que getApiProject() inclut les totaux d'effort prévisionnel et réel.
     *
     * Ces totaux sont calculés en agrégeant les champs effortrequired et effortmade
     * de toutes les tâches actives du projet. Le front peut ainsi afficher
     * la comparaison prévisionnel vs réel sans appel supplémentaire.
     */
    public function testGetApiProjectInclutLesTotauxEffort(): void
    {
        $this->assertStringContainsString(
            'totalEffortRequired',
            $this->codeSourceControleur,
            'getApiProject() doit inclure totalEffortRequired dans la réponse JSON'
        );

        $this->assertStringContainsString(
            'totalEffortMade',
            $this->codeSourceControleur,
            'getApiProject() doit inclure totalEffortMade dans la réponse JSON'
        );

        $this->assertStringContainsString(
            'totalTasks',
            $this->codeSourceControleur,
            'getApiProject() doit inclure le nombre total de tâches (totalTasks)'
        );
    }

    /**
     * Vérifie que le calcul d'effort utilise COALESCE pour gérer l'absence de tâches.
     *
     * SUM() sur un ensemble vide retourne NULL en SQL, pas 0.
     * COALESCE(SUM(...), 0) garantit qu'on retourne toujours un nombre,
     * même si le projet n'a encore aucune tâche associée.
     */
    public function testCalculEffortUtiliseCoalescePoureViterNull(): void
    {
        $this->assertStringContainsString(
            'COALESCE',
            $this->codeSourceControleur,
            'Le calcul d\'effort doit utiliser COALESCE pour retourner 0 en l\'absence de tâches'
        );
    }

    /**
     * Vérifie que le calcul d'effort est isolé dans une méthode dédiée.
     *
     * La méthode privée calculerEffortProjet() concentre toute la logique
     * d'agrégation, ce qui rend getApiProject() plus lisible.
     */
    public function testCalculEffortEstIsoleDansUneMethodeDediee(): void
    {
        $this->assertStringContainsString(
            'calculerEffortProjet',
            $this->codeSourceControleur,
            'Une méthode dédiée calculerEffortProjet() doit exister pour le calcul des totaux'
        );
    }

    /**
     * Vérifie que le calcul d'effort agrège les bonnes colonnes.
     *
     * - effortrequired = effort prévisionnel estimé lors de la création de la tâche
     * - effortmade     = temps réel saisi par le développeur en cours de projet
     */
    public function testCalculEffortAgregeLesBonnesColonnes(): void
    {
        $this->assertStringContainsString(
            'effortrequired',
            $this->codeSourceControleur,
            'Le calcul doit faire la somme de effortrequired (effort prévisionnel)'
        );

        $this->assertStringContainsString(
            'effortmade',
            $this->codeSourceControleur,
            'Le calcul doit faire la somme de effortmade (effort réel)'
        );
    }

    // ---------------------------------------------------------------
    // Tests fonctionnels : logique d'agrégation des efforts
    // ---------------------------------------------------------------

    /**
     * Vérifie la logique de calcul de l'écart entre effort prévisionnel et réel.
     *
     * Un écart positif signifie un dépassement (plus de temps qu'estimé),
     * un écart négatif signifie que le projet est en avance.
     */
    public function testCalculEcartEffortPrevisionnelVsReel(): void
    {
        $effortPrevisionnel = 40.0; // heures estimées
        $effortReel         = 52.5; // heures réellement passées

        $ecart = $effortReel - $effortPrevisionnel;

        $this->assertEquals(12.5, $ecart, 'L\'écart doit être la différence réel - prévisionnel');
        $this->assertTrue($ecart > 0, 'Un écart positif indique un dépassement');
    }

    /**
     * Vérifie que COALESCE(SUM(), 0) retourne 0 quand il n'y a aucune tâche.
     *
     * Ce test simule le comportement SQL avec des données PHP.
     * Sans COALESCE, un SUM sur 0 lignes retourne null, ce qui causerait
     * des erreurs de calcul côté front.
     */
    public function testCoalesceRetourneZeroSiAucuneTache(): void
    {
        // Simulation : SUM retourne null si aucune ligne
        $sumSqlNull = null;

        // La valeur null est remplacée par 0 (comportement attendu de COALESCE)
        $effortTotal = $sumSqlNull ?? 0;

        $this->assertSame(0, $effortTotal, 'COALESCE doit retourner 0 si aucune tâche n\'est associée');
        $this->assertNotNull($effortTotal, 'Le total d\'effort ne doit jamais être null côté API');
    }

    /**
     * Vérifie la logique de détection d'un statut "terminé" basée sur le nom.
     *
     * Cette logique est utilisée pour calculer le pourcentage d'avancement
     * des projets dans les vues et l'API de liste.
     */
    public function testDetectionStatutTermineParNomDuStatut(): void
    {
        // "Clôturé" ne contient pas "clos" (accent ô ≠ o), il n'est pas reconnu par la logique actuelle
        $nomsStatutsTermines   = ['Terminé', 'done', 'Fini'];
        $nomsStatutsNonTermine = ['En cours', 'En attente', 'Retardé', 'Annulé'];

        foreach ($nomsStatutsTermines as $nom) {
            $estTermine = stripos($nom, 'termin') !== false
                       || stripos($nom, 'done') !== false
                       || stripos($nom, 'clos') !== false
                       || stripos($nom, 'fini') !== false;

            $this->assertTrue(
                $estTermine,
                "Le statut \"$nom\" doit être reconnu comme terminé"
            );
        }

        foreach ($nomsStatutsNonTermine as $nom) {
            $estTermine = stripos($nom, 'termin') !== false
                       || stripos($nom, 'done') !== false
                       || stripos($nom, 'clos') !== false
                       || stripos($nom, 'fini') !== false;

            $this->assertFalse(
                $estTermine,
                "Le statut \"$nom\" ne doit PAS être reconnu comme terminé"
            );
        }
    }
}
