<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Absence;
use Kentec\App\Model\Client;
use Kentec\App\Model\Project;
use Kentec\App\Model\State;
use Kentec\App\Model\Task;
use Kentec\App\Model\User;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class HomeController extends AbstractController
{
    private static function isStateCompleted(?string $stateName): bool
    {
        if ($stateName === null) {
            return false;
        }
        $n = strtolower($stateName);
        return str_contains($n, 'termin')
            || str_contains($n, 'done')
            || str_contains($n, 'clos')
            || str_contains($n, 'fini');
    }

    #[OA\Get(
        path: '/',
        summary: 'Show home dashboard',
        tags: ['Home'],
        responses: [
            new OA\Response(response: 200, description: 'Home dashboard page'),
        ]
    )]
    final public function index(): void
    {
        $user     = $_SESSION['USER'];
        $roleName = $user->getRoleName() ?? 'USER';

        $taskRepo    = new Repository(Task::class);
        $projectRepo = new Repository(Project::class);
        $userRepo    = new Repository(User::class);

        $viewData = [
            'pageTitle' => 'Dashboard',
            'user'      => $user,
            'role'      => $roleName,
        ];

        switch ($roleName) {
            case 'PDG':
                $viewData = array_merge($viewData, $this->buildPdgData($taskRepo, $projectRepo, $userRepo));
                break;

            case 'ADMIN':
                $viewData = array_merge($viewData, $this->buildAdminData($userRepo));
                break;

            case 'CDP':
                $viewData = array_merge($viewData, $this->buildCdpData($user, $taskRepo, $projectRepo, $userRepo));
                break;

            default: // USER / Collaborateur
                $viewData = array_merge($viewData, $this->buildUserData($user, $taskRepo, $projectRepo, $userRepo));
                break;
        }

        $this->render('home/index.php', $viewData);
    }

    // ─── PDG : indicateurs stratégiques globaux ───────────────────────────────

    private function buildPdgData(
        Repository $taskRepo,
        Repository $projectRepo,
        Repository $userRepo
    ): array {
        $clientRepo = new Repository(Client::class);

        $totalUsers = count($userRepo->customQuery('SELECT id FROM users WHERE isactive = true') ?? []);
        $totalProjects = count($projectRepo->customQuery('SELECT id FROM project') ?? []);

        $allTasks = $taskRepo->customQuery(
            'SELECT t.id, s.name AS state_name
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id'
        ) ?? [];

        $totalTasks = count($allTasks);
        $completedTasks = 0;
        foreach ($allTasks as $t) {
            if (self::isStateCompleted($t['state_name'] ?? null)) {
                ++$completedTasks;
            }
        }
        $completionRate = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;

        $totalClients = count($clientRepo->customQuery('SELECT siret FROM client') ?? []);

        // Projets en retard
        $lateProjects = count($projectRepo->customQuery(
            'SELECT id FROM project WHERE theoreticaldeadline < NOW() AND realdeadline IS NULL'
        ) ?? []);

        // Tâches haute priorité ouvertes
        $highPriorityOpen = count($taskRepo->customQuery(
            "SELECT t.id FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             WHERE LOWER(t.priority) = 'high'
             AND (s.name IS NULL OR (LOWER(s.name) NOT LIKE '%termin%' AND LOWER(s.name) NOT LIKE '%done%' AND LOWER(s.name) NOT LIKE '%clos%'))"
        ) ?? []);

        // Répartition par statut (top 5 états)
        $tasksByState = $taskRepo->customQuery(
            'SELECT s.name AS state_name, COUNT(t.id) AS cnt
             FROM task t
             LEFT JOIN state s ON t.state_id = s.id
             GROUP BY s.name
             ORDER BY cnt DESC
             LIMIT 5'
        ) ?? [];

        return [
            'kpis' => [
                'totalUsers'      => $totalUsers,
                'totalProjects'   => $totalProjects,
                'totalTasks'      => $totalTasks,
                'completedTasks'  => $completedTasks,
                'completionRate'  => $completionRate,
                'totalClients'    => $totalClients,
                'lateProjects'    => $lateProjects,
                'highPriorityOpen'=> $highPriorityOpen,
            ],
            'tasksByState' => $tasksByState,
        ];
    }

    // ─── ADMIN : gestion des comptes ─────────────────────────────────────────

    private function buildAdminData(Repository $userRepo): array
    {
        $activeUsers = $userRepo->customQuery(
            'SELECT u.id, u.firstname, u.lastname, u.email, u.createdat, r.name AS role_name
             FROM users u
             LEFT JOIN role r ON u.role_id = r.id
             WHERE u.isactive = true
             ORDER BY u.createdat DESC
             LIMIT 10'
        ) ?? [];

        $userCountByRole = $userRepo->customQuery(
            'SELECT r.name AS role_name, COUNT(u.id) AS cnt
             FROM users u
             LEFT JOIN role r ON u.role_id = r.id
             WHERE u.isactive = true
             GROUP BY r.name'
        ) ?? [];

        $totalActive = array_sum(array_column($userCountByRole, 'cnt'));

        return [
            'recentUsers'     => $activeUsers,
            'userCountByRole' => $userCountByRole,
            'totalActiveUsers'=> $totalActive,
        ];
    }

    // ─── CDP : mes projets + charge équipe ────────────────────────────────────

    private static function fetchAbsentUserIds(): array
    {
        $absenceRepo = new Repository(Absence::class);
        $rows = $absenceRepo->customQuery(
            "SELECT user_id FROM absence WHERE CURRENT_DATE BETWEEN startdate AND enddate"
        ) ?? [];
        return array_column($rows, 'user_id');
    }

    private function buildCdpData(
        $user,
        Repository $taskRepo,
        Repository $projectRepo,
        Repository $userRepo
    ): array {
        $userId = $user->getId();
        $absentUserIds = self::fetchAbsentUserIds();

        $myProjects = $projectRepo->customQuery(
            'SELECT p.*, COUNT(t.id) AS task_count,
                    SUM(CASE WHEN LOWER(s.name) LIKE \'%termin%\' OR LOWER(s.name) LIKE \'%done%\' THEN 1 ELSE 0 END) AS done_count
             FROM project p
             LEFT JOIN task t ON t.project_id = p.id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE p.project_manager_id = :userId
             GROUP BY p.id
             ORDER BY p.id DESC',
            ['userId' => $userId]
        ) ?? [];

        foreach ($myProjects as &$p) {
            $total = (int) $p['task_count'];
            $done  = (int) $p['done_count'];
            $p['progress'] = $total > 0 ? round($done / $total * 100) : 0;
        }
        unset($p);

        // Tâches non assignées sur mes projets
        $myProjectIds    = array_column($myProjects, 'id');
        $urgentTasks     = [];
        $unassignedTasks = [];
        if (!empty($myProjectIds)) {
            $placeholders = implode(',', array_fill(0, count($myProjectIds), '?'));
            $urgentTasks  = $taskRepo->customQuery(
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
                array_values($myProjectIds)
            ) ?? [];
            foreach ($urgentTasks as &$ut) {
                $ut['dev_absent'] = !empty($ut['dev_id']) && in_array($ut['dev_id'], $absentUserIds, true);
            }
            unset($ut);

            $unassignedTasks = $taskRepo->customQuery(
                "SELECT t.id, t.name, t.description, t.priority, t.theoreticalenddate, t.effortrequired, s.name AS state_name, p.name AS project_name
                 FROM task t
                 LEFT JOIN state s ON t.state_id = s.id
                 LEFT JOIN project p ON p.id = t.project_id
                 LEFT JOIN usertaskREL ur ON ur.task_id = t.id
                 WHERE t.project_id IN ($placeholders)
                 AND ur.task_id IS NULL
                 AND (s.name IS NULL OR LOWER(s.name) NOT LIKE '%termin%')
                 ORDER BY t.theoreticalenddate ASC",
                array_values($myProjectIds)
            ) ?? [];
        }

        // Stats tâches sur mes projets
        $completedTasksCount = 0;
        $totalTasksCount     = 0;
        foreach ($myProjects as $p) {
            $totalTasksCount     += (int) $p['task_count'];
            $completedTasksCount += (int) $p['done_count'];
        }

        return [
            'myProjects'          => $myProjects,
            'urgentTasks'         => $urgentTasks,
            'unassignedTasks'     => $unassignedTasks,
            'projectsCount'       => count($myProjects),
            'completedTasksCount' => $completedTasksCount,
            'totalTasksCount'     => $totalTasksCount,
        ];
    }

    // ─── USER / Collaborateur : mes tâches du jour ────────────────────────────

    private function buildUserData(
        $user,
        Repository $taskRepo,
        Repository $projectRepo,
        Repository $userRepo
    ): array {
        $absentUserIds = self::fetchAbsentUserIds();
        $tasks = $taskRepo->customQuery(
            'SELECT t.*, s.name AS state_name FROM task t
             INNER JOIN usertaskREL ut ON t.id = ut.task_id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE ut.user_id = :userId
             ORDER BY t.begindate DESC',
            ['userId' => $user->getId()]
        ) ?? [];

        $projects    = $projectRepo->getAll() ?? [];
        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->getId()] = $project;
        }

        $allUsers   = $userRepo->getAll() ?? [];
        $usersById  = [];
        foreach ($allUsers as $u) {
            $usersById[$u->getId()] = $u;
        }

        $projectsCount = 0;
        foreach ($projects as $project) {
            if ($project->getProjectManagerId() === $user->getId()) {
                ++$projectsCount;
            }
        }

        // Calcul activité hebdomadaire (tâches terminées cette semaine / total assignées)
        $weekStart = (new \DateTime())->modify('monday this week')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $weekTasksAll = $taskRepo->customQuery(
            'SELECT t.id, s.name AS state_name FROM task t
             INNER JOIN usertaskREL ut ON t.id = ut.task_id
             LEFT JOIN state s ON t.state_id = s.id
             WHERE ut.user_id = :userId AND t.updatedat >= :weekStart',
            ['userId' => $user->getId(), 'weekStart' => $weekStart]
        ) ?? [];
        $weekDone = 0;
        foreach ($weekTasksAll as $wt) {
            if (self::isStateCompleted($wt['state_name'] ?? null)) {
                ++$weekDone;
            }
        }
        $weekTotal = count($weekTasksAll);
        $weeklyActivity = $weekTotal > 0 ? round($weekDone / $weekTotal * 100) : 0;

        $completedTasksCount = 0;
        foreach ($tasks as &$task) {
            $stateName   = $task['state_name'] ?? '';
            $isCompleted = false !== stripos($stateName, 'termin')
                           || false !== stripos($stateName, 'done')
                           || false !== stripos($stateName, 'clos')
                           || false !== stripos($stateName, 'fini');
            if ($isCompleted) {
                ++$completedTasksCount;
            }

            if (!empty($task['project_id']) && isset($projectsById[$task['project_id']])) {
                $task['project_name'] = $projectsById[$task['project_id']]->getName();
                $pmId = $projectsById[$task['project_id']]->getProjectManagerId();
                $task['pm_name'] = $pmId && isset($usersById[$pmId])
                    ? $usersById[$pmId]->getFirstname() . ' ' . $usersById[$pmId]->getLastname()
                    : 'Non assigné';
                $task['pm_absent'] = $pmId && in_array($pmId, $absentUserIds, true);
            } else {
                $task['project_name'] = 'Sans projet';
                $task['pm_name']      = 'N/A';
                $task['pm_absent']    = false;
            }
        }
        unset($task);

        return [
            'tasks'               => $tasks,
            'completedTasksCount' => $completedTasksCount,
            'projectsCount'       => $projectsCount,
            'weeklyActivity'      => $weeklyActivity,
        ];
    }

    #[OA\Post(
        path: '/create',
        summary: 'Create a new resource',
        tags: ['Home'],
        responses: [
            new OA\Response(response: 200, description: 'Resource created successfully'),
        ]
    )]
    final public function create(): void
    {
        $this->json(['message' => 'create']);
    }
}
