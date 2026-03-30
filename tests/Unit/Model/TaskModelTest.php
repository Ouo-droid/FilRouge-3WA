<?php

namespace Kentec\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Kentec\App\Model\Task;

/**
 * Tests unitaires pour le modèle Task.
 * Vérifie que les typos corrigées (difficulty, theoreticalEndDate) fonctionnent,
 * et que le mapping SQL ↔ PHP est cohérent.
 */
class TaskModelTest extends TestCase
{
    /**
     * Teste que les propriétés corrigées (difficulty, theoreticalEndDate) fonctionnent.
     * Avant le fix : "dificulty" et "theoricalEndDate" (typos).
     */
    public function testCorrectedPropertyNamesWork(): void
    {
        $task = new Task();

        // difficulty (anciennement "dificulty")
        $task->setDifficulty('Moyenne');
        $this->assertEquals('Moyenne', $task->getDifficulty());

        // theoreticalEndDate (anciennement "theoricalEndDate")
        $endDate = new \DateTime('2026-06-15');
        $task->setTheoreticalEndDate($endDate);
        $this->assertEquals($endDate, $task->getTheoreticalEndDate());
    }

    /**
     * Teste que fromDatabaseArray() mappe les nouvelles colonnes SQL corrigées.
     * La colonne SQL est "theoreticalenddate" (pas "theoricalenddate").
     */
    public function testFromDatabaseArrayMapsNewColumnNames(): void
    {
        $databaseRow = [
            'id'                  => 'abc-123',
            'name'                => 'Développer la page login',
            'difficulty'          => 'Facile',
            'begindate'           => '2026-03-01',
            'theoreticalenddate'  => '2026-03-15',
            'realenddate'         => null,
            'project_id'          => 'proj-456',
            'state_id'            => 'state-789',
        ];

        $mappedData = Task::fromDatabaseArray($databaseRow);

        // Vérifie le mapping snake_case → camelCase
        $this->assertEquals('2026-03-01', $mappedData['beginDate']);
        $this->assertEquals('2026-03-15', $mappedData['theoreticalEndDate']);
        $this->assertEquals('proj-456', $mappedData['projectId']);
        $this->assertEquals('state-789', $mappedData['stateId']);
    }

    /**
     * Teste que toDatabaseArray() produit les colonnes SQL avec les noms corrigés.
     */
    public function testToDatabaseArrayUsesCorrectColumnNames(): void
    {
        $task = new Task();
        $task->setName('Test task');
        $task->setType('Development');
        $task->setDifficulty('Difficile');
        $task->setEffortrequired(5.0);
        $task->setBeginDate(new \DateTime('2026-01-01'));
        $task->setTheoreticalEndDate(new \DateTime('2026-01-15'));

        $databaseArray = $task->toDatabaseArray();

        // Vérifie les colonnes corrigées
        $this->assertArrayHasKey('difficulty', $databaseArray);
        $this->assertEquals('Difficile', $databaseArray['difficulty']);
        $this->assertArrayHasKey('theoreticalenddate', $databaseArray);
        $this->assertEquals('2026-01-15', $databaseArray['theoreticalenddate']);

        // Vérifie que les anciennes typos ne sont plus présentes
        $this->assertArrayNotHasKey('dificulty', $databaseArray);
        $this->assertArrayNotHasKey('theoricalenddate', $databaseArray);
    }

    /**
     * Teste que toArray() inclut les clés de compatibilité pour le frontend.
     * Les anciennes clés ("dificulty", "theoricalEndDate") sont maintenues temporairement.
     */
    public function testToArrayIncludesBackwardCompatKeys(): void
    {
        $task = new Task();
        $task->setDifficulty('Facile');
        $task->setTheoreticalEndDate(new \DateTime('2026-06-30'));

        $frontendArray = $task->toArray();

        // Nouvelles clés correctes
        $this->assertArrayHasKey('difficulty', $frontendArray);
        $this->assertArrayHasKey('theoreticalEndDate', $frontendArray);

        // Anciennes clés de compatibilité (pour les Views non migrées)
        $this->assertArrayHasKey('dificulty', $frontendArray);
        $this->assertArrayHasKey('theoricalEndDate', $frontendArray);

        // Même valeur des deux côtés
        $this->assertEquals($frontendArray['difficulty'], $frontendArray['dificulty']);
    }
}
