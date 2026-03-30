<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Model;

use Kentec\App\Model\Client;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le modèle Client.
 * Vérifie que le mapping entre les colonnes SQL et les propriétés PHP fonctionne correctement.
 */
class ClientTest extends TestCase
{
    /**
     * Teste que fromDatabaseArray() mappe correctement les colonnes SQL (minuscules)
     * vers les noms de propriétés PHP (camelCase).
     */
    public function testFromDatabaseArrayMapsColumnsToCamelCase(): void
    {
        $databaseRow = [
            'siret'            => '12345678901234',
            'companyname'      => 'ACME Corp',
            'workfield'        => 'Informatique',
            'contactfirstname' => 'Jean',
            'contactlastname'  => 'Dupont',
            'contactemail'     => 'jean@acme.com',
            'contactphone'     => '0123456789',
        ];

        $mappedData = Client::fromDatabaseArray($databaseRow);

        $this->assertEquals('12345678901234', $mappedData['siret']);
        $this->assertEquals('ACME Corp', $mappedData['companyName']);
        $this->assertEquals('Informatique', $mappedData['workfield']);
        $this->assertEquals('Jean', $mappedData['contactFirstname']);
        $this->assertEquals('Dupont', $mappedData['contactLastname']);
        $this->assertEquals('jean@acme.com', $mappedData['contactEmail']);
        $this->assertEquals('0123456789', $mappedData['contactPhone']);
    }

    /**
     * Teste que toDatabaseArray() produit les bonnes clés SQL (minuscules)
     * et exclut les valeurs null.
     */
    public function testToDatabaseArrayProducesCorrectSqlKeys(): void
    {
        $client = new Client();
        $client->setSiret('98765432109876');
        $client->setCompanyName('Globex');
        $client->setContactFirstname('Marie');
        $client->setContactLastname('Martin');
        $client->setContactEmail('marie@globex.com');
        $client->setContactPhone('0987654321');

        $databaseArray = $client->toDatabaseArray();

        // Vérifie que les clés sont en minuscules (format SQL)
        $this->assertEquals('98765432109876', $databaseArray['siret']);
        $this->assertEquals('Globex', $databaseArray['companyname']);
        $this->assertEquals('Marie', $databaseArray['contactfirstname']);
        $this->assertEquals('marie@globex.com', $databaseArray['contactemail']);

        // Vérifie que les champs null sont exclus (workfield non défini)
        $this->assertArrayNotHasKey('workfield', $databaseArray);
    }

    /**
     * Teste que toArray() produit les bonnes clés pour le frontend (camelCase).
     */
    public function testToArrayProducesCamelCaseKeys(): void
    {
        $client = new Client();
        $client->setSiret('12345678901234');
        $client->setCompanyName('TestCorp');

        $frontendArray = $client->toArray();

        $this->assertArrayHasKey('siret', $frontendArray);
        $this->assertArrayHasKey('companyName', $frontendArray);
        $this->assertArrayHasKey('contactFirstname', $frontendArray);
        $this->assertArrayHasKey('contactEmail', $frontendArray);
        $this->assertArrayHasKey('isactive', $frontendArray);
    }

    /**
     * Teste que isactive est true par défaut.
     */
    public function testIsactiveTrueByDefault(): void
    {
        $client = new Client();
        $this->assertTrue($client->getIsactive());
    }

    /**
     * Teste le setter/getter isactive — soft delete.
     */
    public function testSetIsactiveToFalse(): void
    {
        $client = new Client();
        $client->setIsactive(false);
        $this->assertFalse($client->getIsactive());
        $this->assertFalse($client->toArray()['isactive']);
    }

    /**
     * Teste les getters et setters de base.
     */
    public function testGettersAndSetters(): void
    {
        $client = new Client();
        $client->setSiret('12345678901234');
        $client->setCompanyName('Ma Société');
        $client->setWorkfield('Informatique');
        $client->setContactFirstname('Alice');
        $client->setContactLastname('Durand');
        $client->setContactEmail('alice@test.com');
        $client->setContactPhone('0600000000');

        $this->assertEquals('12345678901234', $client->getSiret());
        $this->assertEquals('Ma Société', $client->getCompanyName());
        $this->assertEquals('Informatique', $client->getWorkfield());
        $this->assertEquals('Alice', $client->getContactFirstname());
        $this->assertEquals('Durand', $client->getContactLastname());
        $this->assertEquals('alice@test.com', $client->getContactEmail());
        $this->assertEquals('0600000000', $client->getContactPhone());
    }

    /**
     * Teste que la constante TABLE est correctement définie.
     */
    public function testTableConstant(): void
    {
        $this->assertEquals('client', Client::TABLE);
    }

    /**
     * Teste que fromDatabaseArray ne plante pas avec des colonnes inconnues.
     */
    public function testFromDatabaseArrayHandlesUnknownColumns(): void
    {
        $databaseRow = [
            'siret'       => '12345678901234',
            'companyname' => 'Test',
            'unknown_col' => 'value',
        ];

        $result = Client::fromDatabaseArray($databaseRow);

        $this->assertEquals('12345678901234', $result['siret']);
        $this->assertEquals('Test', $result['companyName']);
        $this->assertArrayHasKey('unknown_col', $result);
    }
}
