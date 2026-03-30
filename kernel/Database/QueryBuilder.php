<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database;

/**
 * QueryBuilder - Constructeur de requêtes SQL orienté objet (Doctrine-like)
 *
 * Stateful : maintient l'état de la requête en construction
 * Fluent : permet le chaînage des méthodes
 * Orienté objet : API claire et intuitive
 */
class QueryBuilder
{
    /** @var string Table principale */
    private string $table;

    /** @var string Type de requête (SELECT, INSERT, UPDATE, DELETE) */
    private string $type = 'SELECT';

    /** @var array Colonnes à sélectionner */
    private array $select = ['*'];

    /** @var array Conditions WHERE */
    private array $where = [];

    /** @var array Paramètres bindés */
    private array $params = [];

    /** @var array Clauses ORDER BY */
    private array $orderBy = [];

    /** @var array Clauses GROUP BY */
    private array $groupBy = [];

    /** @var array Clauses HAVING */
    private array $having = [];

    /** @var array Clauses JOIN */
    private array $joins = [];

    /** @var int|null Limite de résultats */
    private ?int $limit = null;

    /** @var int Offset pour la pagination */
    private int $offset = 0;

    /** @var bool Mode DISTINCT */
    private bool $distinct = false;

    /** @var int Compteur pour générer des noms de paramètres uniques */
    private int $paramCounter = 0;

    /**
     * Constructeur
     *
     * @param string $table Nom de la table principale
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Définit les colonnes à sélectionner
     */
    public function select(array $columns): self
    {
        $this->select = $columns;
        $this->type = 'SELECT';

        return $this;
    }

    /**
     * Active le mode DISTINCT
     */
    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Ajoute une condition WHERE avec ILIKE sur plusieurs colonnes (OR)
     *
     * ILIKE est l'équivalent PostgreSQL de LIKE mais insensible à la casse.
     * Utilise ce variant dès que la recherche doit ignorer majuscules/minuscules.
     */
    public function whereILike(array $columns, string $value): self
    {
        if (empty($columns) || empty($value)) {
            return $this;
        }

        $conditions = [];
        foreach ($columns as $column) {
            $paramName = $this->generateParamName('ilike');
            $conditions[] = "{$column} ILIKE :{$paramName}";
            $this->params[$paramName] = "%{$value}%";
        }

        $this->where[] = '(' . implode(' OR ', $conditions) . ')';
        return $this;
    }

    /**
     * Ajoute une condition WHERE avec LIKE sur plusieurs colonnes (OR)
     */
    public function whereLike(array $columns, string $value): self
    {
        if (empty($columns) || empty($value)) {
            return $this;
        }

        $conditions = [];
        foreach ($columns as $column) {
            $paramName = $this->generateParamName('like');
            $conditions[] = "{$column} LIKE :{$paramName}";
            $this->params[$paramName] = "%{$value}%";
        }

        $this->where[] = '(' . implode(' OR ', $conditions) . ')';

        return $this;
    }

    /**
     * Ajoute une condition WHERE avec égalité
     */
    public function whereEquals(string $column, $value): self
    {
        if (null === $value) {
            $this->where[] = "{$column} IS NULL";
        } else {
            $paramName = $this->generateParamName('eq');
            $this->where[] = "{$column} = :{$paramName}";
            $this->params[$paramName] = $value;
        }

        return $this;
    }

    /**
     * Ajoute une condition WHERE avec inégalité
     */
    public function whereNotEquals(string $column, $value): self
    {
        if (null === $value) {
            $this->where[] = "{$column} IS NOT NULL";
        } else {
            $paramName = $this->generateParamName('neq');
            $this->where[] = "{$column} != :{$paramName}";
            $this->params[$paramName] = $value;
        }

        return $this;
    }

    /**
     * Ajoute une condition WHERE IN
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName('in');
            $placeholders[] = ":{$paramName}";
            $this->params[$paramName] = $value;
        }

        $this->where[] = "{$column} IN (" . implode(', ', $placeholders) . ')';

        return $this;
    }

    /**
     * Ajoute une condition WHERE NOT IN
     */
    public function whereNotIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName('notin');
            $placeholders[] = ":{$paramName}";
            $this->params[$paramName] = $value;
        }

        $this->where[] = "{$column} NOT IN (" . implode(', ', $placeholders) . ')';

        return $this;
    }

    /**
     * Ajoute une condition WHERE avec comparaison
     */
    public function where(string $column, string $operator, $value): self
    {
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', '<>', 'LIKE', 'NOT LIKE'];

        if (!in_array(strtoupper($operator), array_map('strtoupper', $allowedOperators))) {
            throw new \InvalidArgumentException("Opérateur invalide : {$operator}");
        }

        $paramName = $this->generateParamName('where');
        $this->where[] = "{$column} {$operator} :{$paramName}";
        $this->params[$paramName] = $value;

        return $this;
    }

    /**
     * Ajoute une condition WHERE BETWEEN
     */
    public function whereBetween(string $column, $min, $max): self
    {
        $paramMin = $this->generateParamName('between_min');
        $paramMax = $this->generateParamName('between_max');

        $this->where[] = "{$column} BETWEEN :{$paramMin} AND :{$paramMax}";
        $this->params[$paramMin] = $min;
        $this->params[$paramMax] = $max;

        return $this;
    }

    /**
     * Ajoute une condition WHERE NULL
     */
    public function whereNull(string $column): self
    {
        $this->where[] = "{$column} IS NULL";

        return $this;
    }

    /**
     * Ajoute une condition WHERE NOT NULL
     */
    public function whereNotNull(string $column): self
    {
        $this->where[] = "{$column} IS NOT NULL";

        return $this;
    }

    /**
     * Ajoute une condition WHERE avec une sous-requête personnalisée (OR)
     */
    public function orWhere(callable $callback): self
    {
        $subQb = new self($this->table);
        $callback($subQb);

        if (!empty($subQb->where)) {
            $this->where[] = 'OR (' . implode(' AND ', $subQb->where) . ')';
            $this->params = array_merge($this->params, $subQb->params);
        }

        return $this;
    }

    /**
     * Ajoute un ORDER BY
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException("Direction invalide : {$direction}");
        }

        $this->orderBy[] = "{$column} {$direction}";

        return $this;
    }

    /**
     * Ajoute un GROUP BY
     */
    public function groupBy($columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $this->groupBy = array_merge($this->groupBy, $columns);

        return $this;
    }

    /**
     * Ajoute une condition HAVING
     */
    public function having(string $condition, array $params = []): self
    {
        $this->having[] = $condition;
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Ajoute un INNER JOIN
     */
    public function innerJoin(string $table, string $condition): self
    {
        $this->joins[] = "INNER JOIN {$table} ON {$condition}";

        return $this;
    }

    /**
     * Ajoute un LEFT JOIN
     */
    public function leftJoin(string $table, string $condition): self
    {
        $this->joins[] = "LEFT JOIN {$table} ON {$condition}";

        return $this;
    }

    /**
     * Ajoute un RIGHT JOIN
     */
    public function rightJoin(string $table, string $condition): self
    {
        $this->joins[] = "RIGHT JOIN {$table} ON {$condition}";

        return $this;
    }

    /**
     * Définit la limite de résultats et l'offset
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Définit la pagination (plus intuitif que limit/offset)
     */
    public function paginate(int $page, int $perPage = 20): self
    {
        $page = max(1, $page);
        $this->limit = $perPage;
        $this->offset = ($page - 1) * $perPage;

        return $this;
    }

    /**
     * Construit la requête SQL et retourne [SQL, paramètres]
     */
    public function build(): array
    {
        $sql = $this->buildSelect();

        return [$sql, $this->params];
    }

    /**
     * Construit une requête SELECT
     */
    private function buildSelect(): string
    {
        $sql = 'SELECT ';

        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }

        $sql .= implode(', ', $this->select);
        $sql .= " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if (null !== $this->limit) {
            $sql .= " LIMIT {$this->limit}";

            if ($this->offset > 0) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    /**
     * Construit une requête COUNT pour la pagination
     */
    public function buildCount(): array
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        return [$sql, $this->params];
    }

    /**
     * Réinitialise le QueryBuilder
     */
    public function reset(): self
    {
        $this->select = ['*'];
        $this->where = [];
        $this->params = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->joins = [];
        $this->limit = null;
        $this->offset = 0;
        $this->distinct = false;
        $this->paramCounter = 0;

        return $this;
    }

    /**
     * Clone le QueryBuilder
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Génère un nom de paramètre unique
     */
    private function generateParamName(string $prefix): string
    {
        return $prefix . '_' . $this->paramCounter++;
    }

    /**
     * Affiche la requête SQL pour le debug
     */
    public function toSql(): string
    {
        [$sql, $params] = $this->build();

        foreach ($params as $key => $value) {
            $quoted = is_string($value) ? "'{$value}'" : $value;
            $sql = str_replace(":{$key}", $quoted, $sql);
        }

        return $sql;
    }

    /**
     * Retourne les paramètres actuels
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function __toString(): string
    {
        return $this->toSql();
    }
}
