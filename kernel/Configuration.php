<?php

declare(strict_types=1);

namespace Kentec\Kernel;

use Dotenv\Dotenv;

class Configuration
{
    /**
     * Charge les variables d'environnement depuis le fichier .env vers $_ENV.
     *
     * Le fichier .env contient les secrets (mots de passe BDD, clé JWT, etc.)
     * et la configuration spécifique à chaque environnement (dev, prod).
     *
     * Si le fichier .env n'existe pas, l'application s'arrête avec un message
     * explicite pour guider le développeur (copier .env.example en .env).
     *
     * @return void
     * @throws \RuntimeException Si le fichier .env est introuvable
     */
    public static function loadConfiguration(): void
    {
        $projectRoot = __DIR__ . '/../';
        $envFilePath = $projectRoot . '.env';

        if (!file_exists($envFilePath)) {
            throw new \RuntimeException(
                "Fichier .env introuvable à la racine du projet. "
                . "Copiez le fichier .env.example en .env et remplissez vos valeurs : "
                . "cp .env.example .env"
            );
        }

        Dotenv::createImmutable($projectRoot)->load();
    }
}
