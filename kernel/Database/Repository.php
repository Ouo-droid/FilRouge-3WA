<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database;

use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;

/**
 * Class Repository
 * Cette classe représente un repository générique permettant d'effectuer des opérations CRUD
 * (Create, Read, Update, Delete) sur une table de la base de données.
 * Elle mappe automatiquement les résultats SQL sur des objets du modèle passé en paramètre.
 */
class Repository
{
    protected ?string $sql;              // Contient la requête SQL en cours d'exécution
    protected ?\PDOStatement $request;  // Contient la requête préparée PDO
    protected string $table;            // Nom de la table associée au modèle

    /**
     * Constructeur de la classe Repository.
     * Initialise la table associée en vérifiant si le modèle possède une constante `TABLE`.
     *
     * @param string $model le namespace complet de la classe du modèle (exemple : `App\Models\User`)
     *
     * @throws \Exception si la constante `TABLE` n'est pas définie dans la classe modèle
     */
    public function __construct(protected readonly string $model)
    {
        if (defined($this->model . '::TABLE')) {
            $this->table = $this->model::TABLE;
        } else {
            throw new \Exception("La classe $this->model doit définir une constante TABLE pour fonctionner.");
        }
    }

    /**
     * Supprime un enregistrement de la table par son ID.
     *
     * @param int|string $id L'identifiant de l'enregistrement à supprimer
     */
    final public function delete(int|string $id): bool
    {
        $query = SqlBuilder::prepareDelete($this->table, $id);
        $this->sql = $query['sql'];

        $this->prepare($query['values']);

        return $this->request->rowCount() > 0;
    }

    /**
     * Récupère un enregistrement par son ID.
     *
     * @param int|string $id L'identifiant de l'enregistrement à récupérer
     *
     * @return object|null L'objet du modèle hydraté ou `null` si aucun résultat n'est trouvé
     */
    final public function getById(int|string $id): ?object
    {
        return $this->getByAttributes(['id' => $id], false);
    }

    /**
     * Récupère tous les enregistrements de la table associée.
     *
     * @return array|null un tableau d'objets hydratés ou `null` si aucun résultat n'est trouvé
     */
    final public function getAll(): ?array
    {
        return $this->getByAttributes([]);
    }

    /**
     * Récupère les enregistrements correspondant aux attributs passés.
     *
     * Le paramètre $active permet de filtrer automatiquement les enregistrements soft-deletés :
     * - true  = uniquement les actifs (comportement par défaut)
     * - false = uniquement les inactifs
     * - null  = tous les enregistrements, sans filtre isactive
     *
     * @param array     $attributes Colonnes et valeurs à filtrer (ex: ['email' => 'test@test.com'])
     * @param bool      $all        Si true retourne tous les résultats, sinon le premier uniquement
     * @param bool|null $active     Filtre soft-delete (true=actifs, false=inactifs, null=tous)
     *
     * @return mixed Tableau d'objets ou objet unique, null si aucun résultat
     */
    final public function getByAttributes(array $attributes, bool $all = true, ?bool $active = true): mixed
    {
        $query = SqlBuilder::prepareSelect($this->table, $attributes);
        $this->sql = $query['sql'];
        $queryValues = $query['values'] ?? [];

        // Filtre automatique sur isactive pour les modèles héritant de historytable.
        // IMPORTANT : on utilise un paramètre préparé (:isactive_filter) au lieu de concaténer
        // directement la valeur dans le SQL. Ça protège contre l'injection SQL.
        if (is_a($this->model, HistoryInterface::class, true) && $active !== null) {
            $isactiveClause = empty($attributes) ? ' WHERE isactive = :isactive_filter' : ' AND isactive = :isactive_filter';
            $this->sql .= $isactiveClause;
            $queryValues[':isactive_filter'] = $active ? 'true' : 'false';
        }

        $this->prepare($queryValues);

        return $this->getResult($all);
    }

    /**
     * Insère un nouvel enregistrement dans la table.
     *
     * @param object $entity une instance du modèle contenant les données à insérer
     */
    final public function insert(object $entity): void
    {
        $query = SqlBuilder::prepareInsert($entity, $this->table);
        $this->sql = $query['sql'];

        $this->prepare($query['values']);
    }

    /**
     * Met à jour un enregistrement existant dans la table.
     *
     * @param object $entity une instance du modèle contenant les nouvelles données
     *
     * @throws \Exception si l'entité ne possède pas de clé primaire ou si une erreur survient
     */
    final public function update(object $entity): void
    {
        $query = SqlBuilder::prepareUpdate($entity, $this->table);
        $this->sql = $query['sql'];

        $this->prepare($query['values']);
    }

    /**
     * Prépare et exécute une requête SQL avec les paramètres donnés.
     *
     * @param array|null $args (optionnel) Les paramètres à associer à la requête
     */
    final public function prepare(?array $args = null): void
    {
        $args = SqlBuilder::sanitize($args);

        $this->request = Database::getConnexion()->prepare($this->sql);
        $this->request->execute($args);
    }

    /**
     * Récupère les résultats de la requête SQL.
     *
     * @param bool $all (optionnel) Si `true`, retourne tous les résultats. Sinon, retourne le premier.
     *
     * @return mixed un tableau d'objets ou un seul objet
     */
    private function getResult(bool $all = true): mixed
    {
        $result = $this->fetchAll();

        if ($result) {
            return $all ? $result : $result[0];
        }

        return null;
    }

    /**
     * Récupère tous les enregistrements de la requête SQL.
     *
     * @return array un tableau d'objets hydratés
     */
    public function fetchAll(bool $isCustom = false): array
    {
        $results = $this->request->fetchAll(\PDO::FETCH_ASSOC);

        return $isCustom ? $results : array_map(fn ($data) => $this->hydrate($data), $results);
    }

    /**
     * Hydrate une ligne de données pour en faire une instance du modèle.
     *
     * @param array $data les données récupérées de la base de données
     *
     * @return object une instance du modèle hydratée
     */
    public function hydrate(array $data): object
    {
        if (method_exists($this->model, 'fromDatabaseArray')) {
            $data = $this->model::fromDatabaseArray($data);
        }

        return Hydrator::hydrate($data, $this->model);
    }

    /**
     * Permet de faire une requête SQL personnalisée.
     *
     * @param string     $sql  La requête SQL à exécuter. VEILLEZ À UTILISER LES PARAMÈTRES NOMMÉS.
     * @param array|null $args (optionnel) Les paramètres à associer à la requête
     *
     * @return mixed les résultats de la requête
     */
    public function customQuery(string $sql, ?array $args = null): mixed
    {
        $this->request = Database::getConnexion()->prepare($sql);
        $this->request->execute($args);

        return $this->fetchAll(true);
    }
}
