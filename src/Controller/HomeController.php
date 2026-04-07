<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\State;
use Kentec\App\Repository\ClientRepository;
use Kentec\App\Model\User;
use Kentec\App\Repository\AbsenceRepository;
use Kentec\App\Repository\ProjectRepository;
use Kentec\App\Repository\TaskRepository;
use Kentec\App\Repository\UserRepository;
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

        $taskRepo    = new TaskRepository();
        $projectRepo = new ProjectRepository();
        $userRepo    = new UserRepository();

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
        TaskRepository $taskRepo,
        ProjectRepository $projectRepo,
        UserRepository $userRepo
    ): array {
        $clientRepo = new ClientRepository();

        $totalUsers    = $userRepo->countActive();
        $totalProjects = $projectRepo->countAll();
        $allTasks      = $taskRepo->findAllWithState();

        $totalTasks = count($allTasks);
        $completedTasks = 0;
        foreach ($allTasks as $t) {
            if (self::isStateCompleted($t['state_name'] ?? null)) {
                ++$completedTasks;
            }
        }
        $completionRate = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;

        $totalClients     = $clientRepo->countAll();
        $lateProjects     = count($projectRepo->findLate());
        $highPriorityOpen = count($taskRepo->findHighPriorityOpen());
        $tasksByState     = $taskRepo->findGroupedByState(5);

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

    private function buildAdminData(UserRepository $userRepo): array
    {
        $activeUsers     = $userRepo->findRecentActiveWithRole(10);
        $userCountByRole = $userRepo->countByRole();

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
        return (new AbsenceRepository())->findActiveTodayUserIds();
    }

    private function buildCdpData(
        $user,
        TaskRepository $taskRepo,
        ProjectRepository $projectRepo,
        UserRepository $userRepo
    ): array {
        $userId        = $user->getId();
        $absentUserIds = self::fetchAbsentUserIds();
        $myProjects    = $projectRepo->findByManagerWithStats($userId);

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
            $urgentTasks = $taskRepo->findUrgentForProjects($myProjectIds);
            foreach ($urgentTasks as &$ut) {
                $ut['dev_absent'] = !empty($ut['dev_id']) && in_array($ut['dev_id'], $absentUserIds, true);
            }
            unset($ut);

            $unassignedTasks = $taskRepo->findUnassignedForProjects($myProjectIds);
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
        TaskRepository $taskRepo,
        ProjectRepository $projectRepo,
        UserRepository $userRepo
    ): array {
        $absentUserIds = self::fetchAbsentUserIds();
        $tasks         = $taskRepo->findWithStateByUserId($user->getId());

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
        $weekStart    = (new \DateTime())->modify('monday this week')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $weekTasksAll = $taskRepo->findWeeklyByUserId($user->getId(), $weekStart);
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
