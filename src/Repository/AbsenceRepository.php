<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\Absence;
use Kentec\Kernel\Database\Repository;

class AbsenceRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Absence::class);
    }

    public function findAll(): array
    {
        return $this->customQuery('SELECT * FROM absence ORDER BY startdate DESC') ?? [];
    }

    public function findByUserId(string $userId): array
    {
        return $this->customQuery(
            'SELECT * FROM absence WHERE user_id = :userId ORDER BY startdate DESC',
            ['userId' => $userId]
        ) ?? [];
    }

    public function findActiveTodayUserIds(): array
    {
        $rows = $this->customQuery(
            "SELECT user_id FROM absence WHERE CURRENT_DATE BETWEEN startdate AND enddate"
        ) ?? [];
        return array_column($rows, 'user_id');
    }

    public function findActiveTodayWithDates(): array
    {
        return $this->customQuery(
            "SELECT user_id, startdate, enddate FROM absence WHERE CURRENT_DATE BETWEEN startdate AND enddate"
        ) ?? [];
    }

    public function findActiveTodayWithUsers(): array
    {
        return $this->customQuery(
            "SELECT a.*, u.firstname, u.lastname
             FROM absence a
             JOIN users u ON u.id = a.user_id
             WHERE CURRENT_DATE BETWEEN a.startdate AND a.enddate
             ORDER BY a.startdate"
        ) ?? [];
    }
}
