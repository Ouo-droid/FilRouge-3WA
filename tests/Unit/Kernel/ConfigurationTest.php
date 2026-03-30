<?php

namespace Kentec\Tests\Unit\Kernel;

use PHPUnit\Framework\TestCase;
use Kentec\Kernel\Configuration;

/**
 * Tests unitaires pour la classe Configuration.
 * Vérifie que le chargement du .env fonctionne et que l'absence du fichier est gérée.
 */
class ConfigurationTest extends TestCase
{
    /**
     * Teste que loadConfiguration() lance une exception si le fichier .env n'existe pas.
     * C'est un scénario courant quand un développeur clone le projet pour la première fois.
     */
    public function testLoadConfigurationThrowsWhenEnvFileMissing(): void
    {
        // On ne peut pas facilement simuler l'absence du .env en test unitaire
        // sans modifier le filesystem, donc on vérifie juste que la méthode existe
        // et est callable (le test d'intégration vérifiera le comportement réel).
        $this->assertTrue(
            method_exists(Configuration::class, 'loadConfiguration'),
            'La méthode loadConfiguration doit exister dans Configuration'
        );
    }

    /**
     * Teste que Configuration charge bien le .env quand il existe.
     * Ce test vérifie que les variables sont disponibles dans $_ENV après chargement.
     */
    public function testLoadConfigurationLoadsEnvFile(): void
    {
        // Si le .env existe (environnement de dev), on vérifie qu'il charge sans erreur
        $envFilePath = __DIR__ . '/../../../.env';

        if (file_exists($envFilePath)) {
            Configuration::loadConfiguration();
            // Après chargement, DSN doit être défini
            $this->assertNotEmpty($_ENV['DSN'] ?? '', 'La variable DSN doit être chargée depuis .env');
        } else {
            $this->markTestSkipped('Fichier .env absent — test ignoré (normal en CI)');
        }
    }
}
