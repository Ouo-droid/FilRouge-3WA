<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\Information;
use Kentec\Kernel\Database\Repository;

class InformationRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Information::class);
    }

    public function findRecentWithUser(int $limit = 5): array
    {
        return $this->customQuery(
            "SELECT i.text, i.type, i.createdat, u.firstname, u.lastname
             FROM information i
             LEFT JOIN users u ON i.user_id = u.id
             WHERE i.isactive = true
             ORDER BY i.createdat DESC
             LIMIT $limit"
        ) ?? [];
    }
}
