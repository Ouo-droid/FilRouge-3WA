<?php

namespace Kentec\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Kentec\App\Model\Project;

/**
 * Tests unitaires pour le modèle Project.
 *
 * Vérifie que :
 * - Le mapping SQL ↔ PHP est cohérent (colonnes snake_case → propriétés camelCase)
 * - toDatabaseArray() produit les bonnes colonnes pour les requêtes INSERT/UPDATE
 * - fromDatabaseArray() mappe correctement les lignes retournées par PostgreSQL
 * - Les champs d'audit hérités de historytable fonctionnent (isactive, createdby, updatedby…)
 */
class ProjectModelTest extends TestCase
{
    // ---------------------------------------------------------------
    // Tests sur theoreticalDeadline (correction de la typo historique)
    // ---------------------------------------------------------------

    /**
     * Vérifie que la propriété "theoreticalDeadline" (orthographe correcte) est bien accessible.
     */
    public function testTheoreticalDeadlineGetterSetterFonctionnent(): void
    {
        $project = new Project();

        $deadline = new \DateTime('2026-12-31');
        $project->setTheoreticalDeadline($deadline);

        $this->assertEquals($deadline, $project->getTheoreticalDeadline());
    }

    /**
     * Vérifie que fromDatabaseArray() mappe "theoreticaldeadline" (colonne SQL) en
     * "theoreticalDeadline" (propriété PHP camelCase).
     */
    public function testFromDatabaseArrayMappeLaColonneSql(): void
    {
        $ligneBaseDeDonnees = [
            'id'                    => 'proj-001',
            'name'                  => 'Refonte site web',
            'begindate'             => '2026-01-15',
            'theoreticaldeadline'   => '2026-06-30',
            'realdeadline'          => null,
            'client_id'             => '12345678901234',
            'project_manager_id'    => 'user-456',
            'state_id'              => 'state-001',
        ];

        $donneesMappees = Project::fromDatabaseArray($ligneBaseDeDonnees);

        $this->assertEquals('2026-01-15', $donneesMappees['beginDate']);
        $this->assertEquals('2026-06-30', $donneesMappees['theoreticalDeadline']);
        $this->assertEquals('12345678901234', $donneesMappees['clientId']);
        $this->assertEquals('user-456', $donneesMappees['projectManagerId']);
        $this->assertEquals('state-001', $donneesMappees['stateId']);
    }

    /**
     * Vérifie que toDatabaseArray() produit les colonnes attendues par PostgreSQL.
     * La colonne doit s'appeler "theoreticaldeadline" (tout en minuscules).
     */
    public function testToDatabaseArrayProduitsLesColonnesSql(): void
    {
        $project = new Project();
        $project->setName('Projet test');
        $project->setBeginDate(new \DateTime('2026-03-01'));
        $project->setTheoreticalDeadline(new \DateTime('2026-09-01'));

        $donneesBdd = $project->toDatabaseArray();

        $this->assertArrayHasKey('theoreticaldeadline', $donneesBdd);
        $this->assertArrayNotHasKey('theoricaldeadline', $donneesBdd, 'La vieille typo ne doit plus exister');
        $this->assertArrayHasKey('begindate', $donneesBdd);
        $this->assertEquals('2026-09-01', $donneesBdd['theoreticaldeadline']);
    }

    // ---------------------------------------------------------------
    // Tests sur toArray() — format retourné à l'API / aux vues
    // ---------------------------------------------------------------

    /**
     * Vérifie que toArray() expose les deux clés attendues pour la deadline théorique.
     * "theoreticalDeadline" est la clé officielle, "theoricalDeadLine" est maintenue
     * pour compatibilité avec les vues existantes.
     */
    public function testToArrayInclutLaCleDeCompatibilitePourLesVues(): void
    {
        $project = new Project();
        $project->setTheoreticalDeadline(new \DateTime('2026-12-25'));

        $tableauApi = $project->toArray();

        $this->assertArrayHasKey('theoreticalDeadline', $tableauApi);
        $this->assertArrayHasKey('theoricalDeadLine', $tableauApi, 'Clé de compatibilité requise par les vues');
        $this->assertEquals($tableauApi['theoreticalDeadline'], $tableauApi['theoricalDeadLine']);
    }

    /**
     * Vérifie que toArray() expose toutes les clés attendues par l'API front.
     */
    public function testToArrayContientToutesLesCles(): void
    {
        $project = new Project();

        $tableauApi = $project->toArray();

        $cleesAttendues = ['id', 'name', 'description', 'beginDate', 'theoreticalDeadline',
                           'realDeadline', 'effortcalculated', 'template', 'clientId',
                           'projectManagerId', 'stateId'];

        foreach ($cleesAttendues as $cle) {
            $this->assertArrayHasKey($cle, $tableauApi, "La clé '$cle' doit être présente dans toArray()");
        }
    }

    // ---------------------------------------------------------------
    // Tests sur les champs d'audit (hérités de historytable)
    // ---------------------------------------------------------------

    /**
     * Vérifie qu'un nouveau projet est actif par défaut.
     * isactive = true est la valeur par défaut dans la base.
     */
    public function testNouveauProjetEstActifParDefaut(): void
    {
        $project = new Project();

        $this->assertTrue($project->getIsactive(), 'Un nouveau projet doit être actif par défaut');
    }

    /**
     * Vérifie que isactive peut être mis à false (soft delete).
     */
    public function testIsactivePeutEtreDesactive(): void
    {
        $project = new Project();
        $project->setIsactive(false);

        $this->assertFalse($project->getIsactive());
    }

    /**
     * Vérifie que les champs d'audit createdby et updatedby fonctionnent.
     */
    public function testChampsAuditCreatedbyUpdatedby(): void
    {
        $project = new Project();
        $idUtilisateur = 'user-uuid-123';

        $project->setCreatedby($idUtilisateur);
        $project->setUpdatedby($idUtilisateur);

        $this->assertEquals($idUtilisateur, $project->getCreatedby());
        $this->assertEquals($idUtilisateur, $project->getUpdatedby());
    }

    // ---------------------------------------------------------------
    // Tests sur toDatabaseArray() — comportement avec/sans ID
    // ---------------------------------------------------------------

    /**
     * Vérifie que l'id n'est pas inclus dans toDatabaseArray() si non défini.
     * Lors d'un INSERT, l'UUID est généré par PostgreSQL (DEFAULT gen_random_uuid()).
     */
    public function testToDatabaseArrayNInclutPasLidSiNonDefini(): void
    {
        $project = new Project();
        $project->setName('Nouveau projet');

        $donneesBdd = $project->toDatabaseArray();

        $this->assertArrayNotHasKey('id', $donneesBdd, "L'id ne doit pas être inclus si null (INSERT sans id)");
    }

    /**
     * Vérifie que l'id est inclus dans toDatabaseArray() s'il est défini.
     * Lors d'un UPDATE, l'id doit être présent pour identifier la ligne.
     */
    public function testToDatabaseArrayInclutLidSiDefini(): void
    {
        $project = new Project();
        $project->setId('proj-uuid-456');
        $project->setName('Projet existant');

        $donneesBdd = $project->toDatabaseArray();

        $this->assertArrayHasKey('id', $donneesBdd);
        $this->assertEquals('proj-uuid-456', $donneesBdd['id']);
    }

    /**
     * Vérifie que toDatabaseArray() filtre les valeurs null.
     * On n'envoie que les colonnes ayant une valeur, pour éviter d'écraser
     * des données existantes avec NULL lors d'un UPDATE partiel.
     */
    public function testToDatabaseArrayFiltreLesChampsNull(): void
    {
        $project = new Project();
        $project->setName('Test filtrage null');
        // realDeadline non défini → doit être absent du tableau

        $donneesBdd = $project->toDatabaseArray();

        $this->assertArrayHasKey('name', $donneesBdd);
        // Les dates non définies (null) ne doivent pas apparaître
        $this->assertArrayNotHasKey('realdeadline', $donneesBdd);
    }

    // ---------------------------------------------------------------
    // Tests sur les associations (FK)
    // ---------------------------------------------------------------

    /**
     * Vérifie que le clientId et le projectManagerId sont correctement
     * mappés en colonnes SQL client_id et project_manager_id.
     */
    public function testLesAssociationsSontMappees(): void
    {
        $project = new Project();
        $project->setClientId('12345678901234');
        $project->setProjectManagerId('user-manager-789');
        $project->setStateId('state-en-attente');

        $donneesBdd = $project->toDatabaseArray();

        $this->assertArrayHasKey('client_id', $donneesBdd);
        $this->assertArrayHasKey('project_manager_id', $donneesBdd);
        $this->assertArrayHasKey('state_id', $donneesBdd);
        $this->assertEquals('12345678901234', $donneesBdd['client_id']);
        $this->assertEquals('user-manager-789', $donneesBdd['project_manager_id']);
    }
}
