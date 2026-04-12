<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\Project;
use Kentec\Kernel\Database\Repository;

class ProjectRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Project::class);
    }

    public function findAllActive(): array
    {
        return $this->customQuery(
            'SELECT * FROM project WHERE isactive = true ORDER BY id DESC'
        ) ?? [];
    }

    public function findActiveByUserId(string $userId): array
    {
        return $this->customQuery(
            'SELECT DISTINCT p.* FROM project p
             INNER JOIN task t ON t.project_id = p.id
             INNER JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE ur.user_id = :userId AND p.isactive = true
             ORDER BY p.id DESC',
            ['userId' => $userId]
        ) ?? [];
    }

    public function findActiveById(string $id): ?array
    {
        $results = $this->customQuery(
            'SELECT * FROM project WHERE id = :id AND isactive = true',
            ['id' => $id]
        );
        return !empty($results) ? $results[0] : null;
    }

    public function countAll(): int
    {
        $rows = $this->customQuery('SELECT id FROM project') ?? [];
        return count($rows);
    }

    public function findLate(): array
    {
        return $this->customQuery(
            'SELECT id FROM project WHERE theoreticaldeadline < NOW() AND realdeadline IS NULL'
        ) ?? [];
    }

    public function findByManagerWithStats(string $userId): array
    {
        return $this->customQuery(
            "SELECT p.*, COUNT(t.id) AS task_count,
                    SUM(CASE WHEN LOWER(s.name) LIKE '%termin%' OR LOWER(s.name) LIKE '%done%' THEN 1 ELSE 0 END) AS done_count
             FROM project p
             LEFT JOIN task t ON t.project_id = p.id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE p.project_manager_id = :userId
             GROUP BY p.id
             ORDER BY p.id DESC",
            ['userId' => $userId]
        ) ?? [];
    }

    public function findClientProjectCounts(): array
    {
        $rows = $this->customQuery(
            'SELECT client_id, COUNT(*) as count FROM project GROUP BY client_id'
        ) ?? [];
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['client_id']] = $row['count'];
        }
        return $counts;
    }

    public function nullifyManagerReferences(string $userId): void
    {
        $this->customQuery(
            'UPDATE project SET project_manager_id = NULL WHERE project_manager_id = :id',
            ['id' => $userId]
        );
    }

    public function nullifyUserReferences(string $userId): void
    {
        $this->customQuery(
            'UPDATE project SET user_id = NULL WHERE user_id = :id',
            ['id' => $userId]
        );
    }

    public function findWithDetailsById(string $projectId): ?array
    {
        $rows = $this->customQuery(
            'SELECT p.*,
                    s.name AS state_name,
                    c.companyname AS client_name,
                    u.firstname AS manager_firstname,
                    u.lastname AS manager_lastname
             FROM project p
             LEFT JOIN state s ON s.id = p.state_id
             LEFT JOIN client c ON c.siret = p.client_id
             LEFT JOIN users u ON u.id = p.project_manager_id
             WHERE p.id = :id AND p.isactive = true',
            ['id' => $projectId]
        );
        return !empty($rows) ? $rows[0] : null;
    }

    public function softDelete(string $projectId, ?string $updatedBy): void
    {
        $this->customQuery(
            'UPDATE project
             SET isactive = false,
                 updatedat = NOW(),
                 updatedby = :updatedby
             WHERE id = :id',
            ['updatedby' => $updatedBy, 'id' => $projectId]
        );
    }

    public function updateProject(\Kentec\App\Model\Project $project, string $projectId): void
    {
        $this->customQuery(
            'UPDATE project
             SET name = :name,
                 description = :description,
                 begindate = :begindate,
                 theoreticaldeadline = :theoreticaldeadline,
                 realdeadline = :realdeadline,
                 client_id = :client_id,
                 project_manager_id = :project_manager_id,
                 state_id = :state_id,
                 updatedat = NOW(),
                 updatedby = :updatedby
             WHERE id = :id',
            [
                'name'                => $project->getName(),
                'description'         => $project->getDescription(),
                'begindate'           => $project->getBeginDate()?->format('Y-m-d'),
                'theoreticaldeadline' => $project->getTheoreticalDeadline()?->format('Y-m-d'),
                'realdeadline'        => $project->getRealDeadline()?->format('Y-m-d'),
                'client_id'           => $project->getClientId(),
                'project_manager_id'  => $project->getProjectManagerId(),
                'state_id'            => $project->getStateId(),
                'updatedby'           => $project->getUpdatedby(),
                'id'                  => $projectId,
            ]
        );
    }

    public function findDefaultStateId(): ?string
    {
        $rows = $this->customQuery(
            "SELECT id FROM state WHERE LOWER(name) LIKE '%attente%' LIMIT 1"
        );
        return $rows[0]['id'] ?? null;
    }

    public function findAllArchived(): array
    {
        return $this->customQuery(
            'SELECT * FROM project WHERE isactive = false ORDER BY id DESC'
        ) ?? [];
    }

    public function findArchivedByUserId(string $userId): array
    {
        return $this->customQuery(
            'SELECT DISTINCT p.* FROM project p
             INNER JOIN task t ON t.project_id = p.id
             INNER JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE ur.user_id = :userId AND p.isactive = false
             ORDER BY p.id DESC',
            ['userId' => $userId]
        ) ?? [];
    }
}
