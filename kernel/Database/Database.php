<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database;

use PDO;

/**
 * Cette classe permet de gérer la connexion à la base de données
 * Elle utilise le pattern Singleton pour n'instancier qu'une seule connexion à la base de données
 * Elle utilise l'objet PDO de PHP pour se connecter et pour exécuter des requêtes SQL
 */
class Database
{
    /**
     * @var \PDO|null
     *                Cette variable statique contient l'instance de la connexion à la base de données
     */
    private static ?\PDO $connexion = null;

    /**
     * @method __construct
     * Le constructeur est privé pour empêcher l'instanciation de la classe
     * Il initialise la connexion à la base de données en utilisant les informations de connexion définies dans les configurations
     * de notre framework
     *
     * @throws \Exception
     */
    private function __construct()
    {
        try {
            self::$connexion = new \PDO($_ENV['DSN'], $_ENV['USERNAME'], $_ENV['PASSWORD']);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur de connexion à la base de données, veuillez vérifier vos paramètres de connexion : {$e->getMessage()}");
        }
    }

    /**
     * @method getConnexion
     * Cette méthode permet de récupérer l'instance de la connexion à la base de données
     * Si la connexion n'existe pas, elle est créée.
     * Son instance est ensuite utilisée pour exécuter des requêtes SQL
     */
    final public static function getConnexion(): \PDO
    {
        if (null === self::$connexion) {
            new static();
        }

        return self::$connexion;
    }
}
