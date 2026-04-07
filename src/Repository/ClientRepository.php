<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\Address;
use Kentec\App\Model\Client;
use Kentec\Kernel\Database\QueryBuilder;
use Kentec\Kernel\Database\Repository;

class ClientRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Client::class);
    }

    public function findAddressBySiret(string $siret): ?array
    {
        $result = $this->customQuery(
            'SELECT a.* FROM address a
             INNER JOIN clientaddressrel r ON r.address_id = a.id
             WHERE r.siret = :siret
             LIMIT 1',
            [':siret' => $siret]
        );
        return !empty($result[0]) ? $result[0] : null;
    }

    public function findExistingAddressId(string $siret): ?string
    {
        $result = $this->customQuery(
            'SELECT a.id FROM address a
             INNER JOIN clientaddressrel r ON r.address_id = a.id
             WHERE r.siret = :siret
             LIMIT 1',
            [':siret' => $siret]
        );
        return $result[0]['id'] ?? null;
    }

    public function deleteBySiret(string $siret): void
    {
        $this->customQuery(
            'DELETE FROM client WHERE siret = :siret',
            [':siret' => $siret]
        );
    }

    public function updateBySiret(Client $client, string $siret): void
    {
        $dbData = $client->toDatabaseArray();
        unset($dbData['siret']);

        $setParts = [];
        $params   = [];
        foreach ($dbData as $key => $value) {
            $setParts[]    = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':siret'] = $siret;

        $this->customQuery(
            'UPDATE client SET ' . implode(', ', $setParts) . ' WHERE siret = :siret',
            $params
        );
    }

    public function insertAddress(Address $address): ?string
    {
        $dbData       = $address->toDatabaseArray();
        $columns      = implode(', ', array_keys($dbData));
        $placeholders = implode(', ', array_map(fn ($k) => ":$k", array_keys($dbData)));
        $params       = [];
        foreach ($dbData as $k => $v) {
            $params[":$k"] = $v;
        }
        $result = $this->customQuery(
            "INSERT INTO address ($columns) VALUES ($placeholders) RETURNING id",
            $params
        );
        return $result[0]['id'] ?? null;
    }

    public function updateAddress(string $addressId, array $fields): void
    {
        $setParts = [];
        $params   = [];
        foreach ($fields as $key => $value) {
            $setParts[]    = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':id'] = $addressId;
        $this->customQuery(
            'UPDATE address SET ' . implode(', ', $setParts) . ' WHERE id = :id',
            $params
        );
    }

    public function linkAddressToClient(string $siret, string $addressId): void
    {
        $this->customQuery(
            'INSERT INTO clientaddressrel (siret, address_id) VALUES (:siret, :address_id)',
            [':siret' => $siret, ':address_id' => $addressId]
        );
    }

    public function countAll(): int
    {
        $rows = $this->customQuery('SELECT siret FROM client') ?? [];
        return count($rows);
    }

    public function searchByTerm(string $term): array
    {
        $queryBuilder = new QueryBuilder(Client::TABLE);
        $queryBuilder
            ->select(['siret', 'companyname', 'workfield', 'contactfirstname', 'contactlastname', 'contactemail', 'contactphone'])
            ->whereEquals('isactive', 'true')
            ->whereILike(['companyname', 'siret', 'workfield'], $term)
            ->orderBy('companyname', 'ASC');

        [$sql, $params] = $queryBuilder->build();
        return $this->customQuery($sql, $params) ?? [];
    }
}
