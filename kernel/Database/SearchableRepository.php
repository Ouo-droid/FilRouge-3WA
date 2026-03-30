<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database;

/**
 * SearchableRepository - Repository avec capacités de recherche utilisant QueryBuilder
 */
class SearchableRepository extends Repository
{
    /**
     * Recherche générique utilisant QueryBuilder
     */
    public function search(
        string $searchTerm = '',
        array $searchableColumns = [],
        array $filters = [],
        string $orderBy = 'id',
        string $orderDirection = 'ASC',
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createQueryBuilder();

        if (!empty($searchTerm) && !empty($searchableColumns)) {
            $qb->whereLike($searchableColumns, $searchTerm);
        }

        $this->applyFilters($qb, $filters);
        $qb->orderBy($orderBy, $orderDirection);

        if (null !== $limit) {
            $qb->limit($limit, $offset);
        }

        return $this->executeQueryBuilder($qb);
    }

    /**
     * Compte le nombre total de résultats
     */
    public function countSearch(
        string $searchTerm = '',
        array $searchableColumns = [],
        array $filters = [],
    ): int {
        $qb = $this->createQueryBuilder();

        if (!empty($searchTerm) && !empty($searchableColumns)) {
            $qb->whereLike($searchableColumns, $searchTerm);
        }

        $this->applyFilters($qb, $filters);

        [$sql, $params] = $qb->buildCount();

        $stmt = Database::getConnexion()->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Recherche avancée avec QueryBuilder personnalisé
     */
    public function advancedSearch(callable $callback): array
    {
        $qb = $this->createQueryBuilder();
        $callback($qb);

        return $this->executeQueryBuilder($qb);
    }

    /**
     * Recherche avec pagination intuitive
     */
    public function searchWithPagination(
        string $searchTerm = '',
        array $searchableColumns = [],
        array $filters = [],
        int $page = 1,
        int $perPage = 20,
        string $orderBy = 'id',
        string $orderDirection = 'ASC',
    ): array {
        $total = $this->countSearch($searchTerm, $searchableColumns, $filters);

        $qb = $this->createQueryBuilder();

        if (!empty($searchTerm) && !empty($searchableColumns)) {
            $qb->whereLike($searchableColumns, $searchTerm);
        }

        $this->applyFilters($qb, $filters);
        $qb->orderBy($orderBy, $orderDirection)
           ->paginate($page, $perPage);

        $data = $this->executeQueryBuilder($qb);

        return [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ],
        ];
    }

    /**
     * Recherche avec agrégations (COUNT, SUM, AVG, etc.)
     */
    public function aggregate(
        string $aggregateFunction,
        string $column = '*',
        array $filters = [],
    ) {
        $qb = $this->createQueryBuilder();
        $qb->select(["{$aggregateFunction}({$column}) as result"]);

        $this->applyFilters($qb, $filters);

        [$sql, $params] = $qb->build();

        $stmt = Database::getConnexion()->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['result'] ?? null;
    }

    /**
     * Recherche avec GROUP BY
     */
    public function groupBy(
        array $groupBy,
        array $select = ['*'],
        array $filters = [],
        array $having = [],
    ): array {
        $qb = $this->createQueryBuilder();
        $qb->select($select);
        $qb->groupBy($groupBy);

        $this->applyFilters($qb, $filters);

        foreach ($having as $condition => $params) {
            $qb->having($condition, $params);
        }

        return $this->executeQueryBuilder($qb);
    }

    /**
     * Recherche avec JOINs
     */
    public function searchWithJoins(callable $callback): array
    {
        $qb = $this->createQueryBuilder();
        $callback($qb);

        return $this->executeQueryBuilder($qb);
    }

    /**
     * Recherche DISTINCT
     */
    public function distinct(array $columns, array $filters = []): array
    {
        $qb = $this->createQueryBuilder();
        $qb->distinct()->select($columns);

        $this->applyFilters($qb, $filters);

        return $this->executeQueryBuilder($qb);
    }

    /**
     * Crée un nouveau QueryBuilder pour la table du modèle
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->table);
    }

    /**
     * Applique les filtres au QueryBuilder
     */
    protected function applyFilters(QueryBuilder $qb, array $filters): void
    {
        foreach ($filters as $column => $value) {
            if (null === $value || '' === $value) {
                continue;
            }

            if (preg_match('/^(.+?)\s*(<>|!=|<=|>=|<|>|=)$/', $column, $matches)) {
                $column = trim($matches[1]);
                $operator = $matches[2];
                $qb->where($column, $operator, $value);
            } elseif (is_array($value)) {
                $qb->whereIn($column, $value);
            } elseif (null === $value) {
                $qb->whereNull($column);
            } else {
                $qb->whereEquals($column, $value);
            }
        }
    }

    /**
     * Exécute un QueryBuilder et retourne les résultats
     */
    protected function executeQueryBuilder(QueryBuilder $qb): array
    {
        [$sql, $params] = $qb->build();

        $this->request = Database::getConnexion()->prepare($sql);
        $this->bindParams($this->request, $params);
        $this->request->execute();

        return $this->fetchAll();
    }

    /**
     * Bind les paramètres à un statement PDO
     */
    protected function bindParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_BOOL);
            } elseif (is_null($value)) {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_STR);
            }
        }
    }

    /**
     * Retourne le QueryBuilder pour usage externe
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder();
    }
}
