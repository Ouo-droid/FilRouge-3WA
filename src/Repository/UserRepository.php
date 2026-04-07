<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\User;
use Kentec\Kernel\Database\Repository;

class UserRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function findActiveWithRole(): array
    {
        return $this->customQuery(
            'SELECT u.*, r.name AS role_name
             FROM users u
             LEFT JOIN role r ON u.role_id = r.id
             WHERE u.isactive = true
             ORDER BY u.lastname, u.firstname'
        ) ?? [];
    }

    public function countByRole(): array
    {
        return $this->customQuery(
            'SELECT r.name AS role_name, COUNT(u.id) AS cnt
             FROM users u
             LEFT JOIN role r ON u.role_id = r.id
             WHERE u.isactive = true
             GROUP BY r.name'
        ) ?? [];
    }

    public function countActive(): int
    {
        $rows = $this->customQuery('SELECT id FROM users WHERE isactive = true') ?? [];
        return count($rows);
    }

    public function findRecentActiveWithRole(int $limit = 10): array
    {
        return $this->customQuery(
            "SELECT u.id, u.firstname, u.lastname, u.email, u.createdat, r.name AS role_name
             FROM users u
             LEFT JOIN role r ON u.role_id = r.id
             WHERE u.isactive = true
             ORDER BY u.createdat DESC
             LIMIT $limit"
        ) ?? [];
    }

    public function deleteAddressReferences(string $userId): void
    {
        $this->customQuery('DELETE FROM useraddress WHERE user_id = :id', ['id' => $userId]);
        $this->customQuery('DELETE FROM useraddressrel WHERE user_id = :id', ['id' => $userId]);
    }
}
