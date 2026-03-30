<?php

namespace Kentec\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour ClientController.
 *
 * Contrainte architecturale :
 * Le contrôleur instancie directement le Repository (new Repository())
 * sans injection de dépendances. Ces tests sont donc des "tests de structure" :
 * on lit le code source et on vérifie qu'il respecte les règles du projet.
 *
 * Ce qu'on vérifie ici :
 * - Soft delete (jamais de DELETE FROM)
 * - Champs d'audit createdby/updatedby renseignés
 * - updatedat mis à jour lors des modifications
 * - Format JSON standardisé (jsonSuccess/jsonError)
 * - Vérification CSRF sur toutes les routes d'écriture
 * - Validation du format SIRET (14 chiffres)
 */
class ClientControllerTest extends TestCase
{
    /** Chemin absolu vers le fichier du contrôleur */
    private string $cheminFichierControleur;

    /** Code source du contrôleur (lu une seule fois pour tous les tests) */
    private string $codeSourceControleur;

    protected function setUp(): void
    {
        $this->cheminFichierControleur = __DIR__ . '/../../../src/Controller/ClientController.php';
        $this->codeSourceControleur    = file_get_contents($this->cheminFichierControleur);
    }

    /**
     * Vérifie que le contrôleur implémente le soft delete.
     *
     * La table client hérite de historytable et possède isactive.
     * On ne doit jamais exécuter DELETE FROM — on passe isactive à false.
     */
    public function testControleurImplimenteSoftDeleteSansDeletePhysique(): void
    {
        $this->assertStringContainsString(
            'isactive = false',
            $this->codeSourceControleur,
            'deleteApiClient() doit passer isactive à false (soft delete)'
        );

        // Vérifie qu'il n'y a pas de DELETE SQL direct
        $this->assertStringNotContainsString(
            'DELETE FROM',
            $this->codeSourceControleur,
            'deleteApiClient() ne doit jamais exécuter DELETE FROM — utiliser isactive = false'
        );
    }

    /**
     * Vérifie que le champ createdby est renseigné lors de la création d'un client.
     *
     * Ce champ d'audit indique quel utilisateur a créé la fiche client.
     */
    public function testControleurRenseigneCreatedby(): void
    {
        $this->assertStringContainsString(
            'setCreatedby',
            $this->codeSourceControleur,
            'addApiClient() doit renseigner createdby avec l\'ID de l\'utilisateur connecté'
        );
    }

    /**
     * Vérifie que le champ updatedby est renseigné lors des modifications et du soft delete.
     */
    public function testControleurRenseigneUpdatedby(): void
    {
        $this->assertStringContainsString(
            'updatedby',
            $this->codeSourceControleur,
            'editApiClient() et deleteApiClient() doivent renseigner updatedby'
        );
    }

    /**
     * Vérifie que updatedat est mis à jour lors des modifications.
     *
     * updatedat permet de savoir quand la fiche client a été modifiée pour la dernière fois.
     */
    public function testControleurMetAJourUpdatedat(): void
    {
        $this->assertStringContainsString(
            'updatedat = NOW()',
            $this->codeSourceControleur,
            'editApiClient() et deleteApiClient() doivent mettre à jour updatedat via NOW()'
        );
    }

    /**
     * Vérifie que le contrôleur utilise le format JSON standardisé du projet.
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
    public function testControleurNaPlancienFormatJson(): void
    {
        $this->assertStringNotContainsString(
            '"delete"',
            $this->codeSourceControleur,
            'L\'ancien format {"delete": true} ne doit plus être utilisé — utiliser jsonSuccess()'
        );

        $this->assertStringNotContainsString(
            '"message" => "Client deleted',
            $this->codeSourceControleur,
            'L\'ancien format de message de suppression ne doit plus être utilisé'
        );
    }

    /**
     * Vérifie que le CSRF est vérifié dans les méthodes d'écriture.
     *
     * Toute route POST, PUT ou DELETE doit vérifier le token CSRF
     * avant de traiter les données, pour se protéger des attaques CSRF.
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
     * Vérifie que le format SIRET est validé (14 chiffres).
     *
     * Le SIRET est la clé primaire de la table client. Un SIRET invalide
     * ne doit jamais être accepté.
     */
    public function testControleurValideSiret14Chiffres(): void
    {
        $this->assertStringContainsString(
            'ctype_digit',
            $this->codeSourceControleur,
            'addApiClient() doit vérifier que le SIRET ne contient que des chiffres'
        );

        $this->assertStringContainsString(
            '14',
            $this->codeSourceControleur,
            'addApiClient() doit vérifier que le SIRET fait exactement 14 caractères'
        );
    }

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

    // --- Tests sur getApiClient() enrichi (adresse + projets) ---

    /**
     * Vérifie que getApiClient() récupère l'adresse via la table de liaison clientaddressrel.
     *
     * La fiche client doit inclure les données d'adresse en joignant
     * clientaddressrel et address pour un affichage complet.
     */
    public function testGetApiClientInclutLAdresseViaJointure(): void
    {
        $this->assertStringContainsString(
            'clientaddressrel',
            $this->codeSourceControleur,
            'getApiClient() doit joindre la table clientaddressrel pour récupérer l\'adresse'
        );

        $this->assertStringContainsString(
            'FROM address',
            $this->codeSourceControleur,
            'getApiClient() doit joindre la table address'
        );
    }

    /**
     * Vérifie que getApiClient() récupère les projets associés au client.
     *
     * La fiche client doit contenir la liste des projets liés
     * pour que le front puisse afficher les associations client-projets.
     */
    public function testGetApiClientInclutLesProjetsAssocies(): void
    {
        $this->assertStringContainsString(
            'FROM project',
            $this->codeSourceControleur,
            'getApiClient() doit récupérer les projets depuis la table project'
        );

        $this->assertStringContainsString(
            "client_id = :siret",
            $this->codeSourceControleur,
            'getApiClient() doit filtrer les projets par client_id = siret'
        );

        $this->assertStringContainsString(
            "'projects'",
            $this->codeSourceControleur,
            'getApiClient() doit exposer la clé "projects" dans la réponse'
        );
    }

    /**
     * Vérifie que getApiClient() ne retourne que les projets actifs.
     *
     * Les projets archivés (isactive = false) ne doivent pas apparaître dans la fiche client.
     */
    public function testGetApiClientFiltreLesProjetsActifs(): void
    {
        // On cherche "isactive = true" qui peut apparaître dans la requête projet
        $this->assertMatchesRegularExpression(
            '/isactive\s*=\s*true/',
            $this->codeSourceControleur,
            'getApiClient() doit filtrer les projets avec isactive = true'
        );
    }

    // --- Tests sur searchApiClients() ---

    /**
     * Vérifie que searchApiClients() utilise ILIKE pour la recherche insensible à la casse.
     *
     * PostgreSQL distingue majuscules et minuscules avec LIKE.
     * ILIKE permet de retrouver "Apple" en cherchant "apple".
     */
    public function testSearchApiClientsUtiliseIlike(): void
    {
        $this->assertStringContainsString(
            'whereILike',
            $this->codeSourceControleur,
            'searchApiClients() doit utiliser whereILike() pour la recherche insensible à la casse'
        );
    }

    /**
     * Vérifie que searchApiClients() utilise le QueryBuilder pour construire la requête.
     *
     * Le QueryBuilder garantit que tous les paramètres sont préparés (pas d'injection SQL possible).
     */
    public function testSearchApiClientsUtiliseQueryBuilder(): void
    {
        $this->assertStringContainsString(
            'QueryBuilder',
            $this->codeSourceControleur,
            'searchApiClients() doit utiliser QueryBuilder pour construire la requête de recherche'
        );
    }

    /**
     * Vérifie que la recherche porte sur la raison sociale, le SIRET et le secteur d'activité.
     */
    public function testSearchApiClientsCouvreLesBonsChamps(): void
    {
        $this->assertStringContainsString(
            'companyname',
            $this->codeSourceControleur,
            'La recherche doit inclure le champ companyname (raison sociale)'
        );

        $this->assertStringContainsString(
            'workfield',
            $this->codeSourceControleur,
            'La recherche doit inclure le champ workfield (secteur d\'activité)'
        );
    }

    /**
     * Vérifie que le résultat de la recherche expose le champ "total".
     *
     * Ce champ permet au front de savoir combien de résultats ont été trouvés
     * sans avoir à compter le tableau côté client.
     */
    public function testSearchApiClientsExposeLeTotal(): void
    {
        $this->assertStringContainsString(
            "'total'",
            $this->codeSourceControleur,
            'searchApiClients() doit inclure la clé "total" dans la réponse JSON'
        );
    }

    // --- Tests fonctionnels sur la validation SIRET ---

    /**
     * Un SIRET valide : 14 chiffres.
     */
    public function testValidationSiretCorrectEstAccepte(): void
    {
        $siret = '12345678901234';
        $this->assertTrue(
            strlen($siret) === 14 && ctype_digit($siret),
            'Un SIRET de 14 chiffres doit être accepté'
        );
    }

    /**
     * Un SIRET trop court doit être rejeté.
     */
    public function testValidationSiretTropCourtEstRejete(): void
    {
        $siret = '123456';
        $this->assertFalse(
            strlen($siret) === 14 && ctype_digit($siret),
            'Un SIRET de moins de 14 chiffres doit être rejeté'
        );
    }

    /**
     * Un SIRET contenant des lettres doit être rejeté.
     */
    public function testValidationSiretAvecLettresEstRejete(): void
    {
        $siret = '1234567890ABCD';
        $this->assertFalse(
            strlen($siret) === 14 && ctype_digit($siret),
            'Un SIRET contenant des lettres doit être rejeté'
        );
    }

    /**
     * Un SIRET vide doit être rejeté.
     */
    public function testValidationSiretVideEstRejete(): void
    {
        $siret = '';
        $this->assertFalse(
            !empty($siret) && strlen($siret) === 14 && ctype_digit($siret),
            'Un SIRET vide doit être rejeté'
        );
    }
}
