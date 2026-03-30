<?php

declare(strict_types=1);

namespace Kentec\Kernel;

/**
 * Class Kernel
 *
 * Cette classe représente le noyau de l'application.
 * Elle charge la configuration et dispatche les requêtes HTTP.
 */
class Kernel
{
    /**
     * Point d'entrée de l'application.
     *
     * boot() exécute les étapes d'initialisation dans l'ordre :
     * 1. Charger les variables d'environnement depuis .env (BDD, JWT secret, etc.)
     * 2. Dispatcher la requête HTTP vers le bon contrôleur via le Router
     *
     * @throws \Exception
     */
    public static function boot(): void
    {
        Configuration::loadConfiguration();

        Router::dispatch();
    }
}
