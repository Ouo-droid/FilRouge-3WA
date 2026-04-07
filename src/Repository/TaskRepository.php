<?php

declare(strict_types=1);

namespace Kentec\App\Repository;

use Kentec\App\Model\Task;
use Kentec\Kernel\Database\Repository;

class TaskRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Task::class);
    }

    public function findByUserId(string $userId): array
    {
        return $this->customQuery(
            'SELECT t.* FROM task t
             INNER JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE ur.user_id = :userId
             ORDER BY t.id DESC',
            ['userId' => $userId]
        ) ?? [];
    }

    public function findAllOrderedById(): array
    {
        return $this->customQuery('SELECT * FROM task ORDER BY id DESC') ?? [];
    }

    public function findAllUserAssignments(): array
    {
        return $this->customQuery('SELECT user_id, task_id FROM usertaskREL') ?? [];
    }

    public function isAssignedToUser(string $taskId, string $userId): bool
    {
        $result = $this->customQuery(
            'SELECT 1 FROM usertaskREL WHERE task_id = :taskId AND user_id = :userId LIMIT 1',
            ['taskId' => $taskId, 'userId' => $userId]
        );
        return !empty($result);
    }

    public function deleteUserAssignment(string $taskId): void
    {
        $this->customQuery(
            'DELETE FROM usertaskrel WHERE task_id = :task_id',
            [':task_id' => $taskId]
        );
    }

    public function insertUserAssignment(string $userId, string $taskId): void
    {
        $this->customQuery(
            'INSERT INTO usertaskrel (user_id, task_id) VALUES (:user_id, :task_id)',
            [':user_id' => $userId, ':task_id' => $taskId]
        );
    }

    public function insertAndReturnId(Task $task): ?string
    {
        $taskData     = $task->toDatabaseArray();
        $columns      = implode(', ', array_keys($taskData));
        $placeholders = implode(', ', array_map(fn ($k) => ":$k", array_keys($taskData)));
        $sql          = "INSERT INTO task ($columns) VALUES ($placeholders) RETURNING id";
        $params       = array_combine(
            array_map(fn ($k) => ":$k", array_keys($taskData)),
            array_values($taskData)
        );
        $result = $this->customQuery($sql, $params);
        return $result[0]['id'] ?? null;
    }

    public function findActiveWithUserAssignments(): array
    {
        return $this->customQuery(
            'SELECT t.id, t.state_id, t.project_id, ut.user_id
             FROM task t
             INNER JOIN usertaskrel ut ON t.id = ut.task_id
             WHERE t.isactive = true'
        ) ?? [];
    }

    public function findWithStateByUserId(string $userId): array
    {
        return $this->customQuery(
            'SELECT t.*, s.name AS state_name FROM task t
             INNER JOIN usertaskREL ut ON t.id = ut.task_id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE ut.user_id = :userId
             ORDER BY t.begindate DESC',
            ['userId' => $userId]
        ) ?? [];
    }

    public function findAllWithState(): array
    {
        return $this->customQuery(
            'SELECT t.id, s.name AS state_name
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id'
        ) ?? [];
    }

    public function findHighPriorityOpen(): array
    {
        return $this->customQuery(
            "SELECT t.id FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             WHERE LOWER(t.priority) = 'high'
             AND (s.name IS NULL OR (LOWER(s.name) NOT LIKE '%termin%' AND LOWER(s.name) NOT LIKE '%done%' AND LOWER(s.name) NOT LIKE '%clos%'))"
        ) ?? [];
    }

    public function findGroupedByState(int $limit = 5): array
    {
        return $this->customQuery(
            "SELECT s.name AS state_name, COUNT(t.id) AS cnt
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             GROUP BY s.name
             ORDER BY cnt DESC
             LIMIT $limit"
        ) ?? [];
    }

    public function findUrgentForProjects(array $projectIds): array
    {
        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        return $this->customQuery(
            "SELECT t.*, s.name AS state_name, u.firstname AS dev_firstname, u.lastname AS dev_lastname, u.id AS dev_id
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             LEFT JOIN usertaskREL ur ON ur.task_id = t.id
             LEFT JOIN users u ON u.id = ur.user_id
             WHERE t.project_id IN ($placeholders)
             AND (LOWER(t.priority) = 'high' OR t.theoreticalenddate <= NOW() + INTERVAL '7 days')
             AND (s.name IS NULL OR LOWER(s.name) NOT LIKE '%termin%')
             ORDER BY t.theoreticalenddate ASC
             LIMIT 10",
            array_values($projectIds)
        ) ?? [];
    }

    public function findUnassignedForProjects(array $projectIds): array
    {
        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        return $this->customQuery(
            "SELECT t.id, t.name, t.description, t.priority, t.theoreticalenddate, t.effortrequired, s.name AS state_name, p.name AS project_name
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             LEFT JOIN project p ON p.id = t.project_id
             LEFT JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE t.project_id IN ($placeholders)
             AND ur.task_id IS NULL
             AND (s.name IS NULL OR LOWER(s.name) NOT LIKE '%termin%')
             ORDER BY t.theoreticalenddate ASC",
            array_values($projectIds)
        ) ?? [];
    }

    public function findWeeklyByUserId(string $userId, string $weekStart): array
    {
        return $this->customQuery(
            'SELECT t.id, s.name AS state_name FROM task t
             INNER JOIN usertaskREL ut ON t.id = ut.task_id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE ut.user_id = :userId AND t.updatedat >= :weekStart',
            ['userId' => $userId, 'weekStart' => $weekStart]
        ) ?? [];
    }

    public function findTaskIdsByUserId(string $userId): array
    {
        $rows = $this->customQuery(
            'SELECT task_id FROM usertaskREL WHERE user_id = :userId',
            ['userId' => $userId]
        ) ?? [];
        return array_column($rows, 'task_id');
    }

    public function findProjectIdsByUserId(string $userId): array
    {
        $rows = $this->customQuery(
            'SELECT DISTINCT t.project_id FROM task t
             INNER JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE ur.user_id = :userId AND t.project_id IS NOT NULL',
            ['userId' => $userId]
        ) ?? [];
        return array_column($rows, 'project_id');
    }

    public function isUserAssignedToProject(string $projectId, string $userId): bool
    {
        $result = $this->customQuery(
            'SELECT 1 FROM task t
             INNER JOIN usertaskREL ur ON ur.task_id = t.id
             WHERE t.project_id = :projectId AND ur.user_id = :userId
             LIMIT 1',
            ['projectId' => $projectId, 'userId' => $userId]
        );
        return !empty($result);
    }

    public function deleteAllUserAssignments(string $userId): void
    {
        $this->customQuery(
            'DELETE FROM usertaskrel WHERE user_id = :id',
            ['id' => $userId]
        );
    }

    public function nullifyDeveloperReferences(string $userId): void
    {
        $this->customQuery(
            'UPDATE task SET developer_id = NULL WHERE developer_id = :id',
            ['id' => $userId]
        );
    }

    public function findEffortByProjectId(string $projectId): array
    {
        $rows = $this->customQuery(
            'SELECT COALESCE(SUM(t.effortrequired), 0) AS totalEffortRequired,
                    COALESCE(SUM(t.effortmade), 0) AS totalEffortMade,
                    COUNT(t.id) AS totalTasks
             FROM task t
             WHERE t.project_id = :projectId',
            ['projectId' => $projectId]
        );
        return [
            'totalEffortRequired' => (float) ($rows[0]['totaleffortrequired'] ?? $rows[0]['totalEffortRequired'] ?? 0),
            'totalEffortMade'     => (float) ($rows[0]['totaleffortmade'] ?? $rows[0]['totalEffortMade'] ?? 0),
            'totalTasks'          => (int)   ($rows[0]['totaltasks'] ?? $rows[0]['totalTasks'] ?? 0),
        ];
    }

    public function updateStateForProject(string $stateId, string $projectId): void
    {
        $this->customQuery(
            'UPDATE task SET state_id = :stateId WHERE project_id = :projectId',
            ['stateId' => $stateId, 'projectId' => $projectId]
        );
    }
}
