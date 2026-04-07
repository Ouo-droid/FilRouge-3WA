<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Project;
use Kentec\App\Model\State;
use Kentec\App\Model\User;
use Kentec\App\Repository\AbsenceRepository;
use Kentec\App\Repository\InformationRepository;
use Kentec\App\Repository\TaskRepository;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use OpenApi\Attributes as OA;

class TeamController extends AbstractController
{
    #[OA\Get(
        path: '/team',
        summary: 'Show team management page',
        tags: ['Team'],
        responses: [
            new OA\Response(response: 200, description: 'Team page'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    final public function index(): void
    {
        $currentUser = \Kentec\Kernel\Security\Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        $data = $this->buildTeamData();
        $this->render('team/team.php', array_merge(['pageTitle' => 'Équipe', 'userRole' => $userRole], $data));
    }

    #[OA\Get(
        path: '/api/team/stats',
        summary: 'Get team stats as JSON (for live polling)',
        tags: ['Team'],
        responses: [
            new OA\Response(response: 200, description: 'Team stats JSON'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    final public function getApiStats(): void
    {
        $this->jsonSuccess($this->buildTeamData());
    }

    private function buildTeamData(): array
    {
        $userRepo    = new Repository(User::class);
        $taskRepo    = new TaskRepository();
        $projectRepo = new Repository(Project::class);
        $stateRepo   = new Repository(State::class);
        $infoRepo    = new InformationRepository();

        // All active users
        $users = $userRepo->getByAttributes([], true, true) ?? [];

        // Identify "done" state IDs
        $states       = $stateRepo->getAll() ?? [];
        $doneStateIds = [];
        foreach ($states as $state) {
            $name = strtolower($state->getName() ?? '');
            if (
                str_contains($name, 'termin') ||
                str_contains($name, 'done')   ||
                str_contains($name, 'fini')   ||
                str_contains($name, 'clos')
            ) {
                $doneStateIds[] = $state->getId();
            }
        }

        // All tasks with user assignments
        $allTasks = $taskRepo->findActiveWithUserAssignments();

        // All projects indexed by ID
        $projects     = $projectRepo->getAll() ?? [];
        $projectsById = [];
        foreach ($projects as $p) {
            $projectsById[$p->getId()] = $p->getName();
        }

        // Per-user aggregates
        $userActiveTasks = [];
        $userDoneTasks   = [];
        $userProjects    = [];

        foreach ($allTasks as $task) {
            $uid = $task['user_id'];
            $sid = $task['state_id'];
            $pid = $task['project_id'];

            if (in_array($sid, $doneStateIds, true)) {
                $userDoneTasks[$uid] = ($userDoneTasks[$uid] ?? 0) + 1;
            } else {
                $userActiveTasks[$uid] = ($userActiveTasks[$uid] ?? 0) + 1;
            }

            if ($pid && isset($projectsById[$pid])) {
                $userProjects[$uid][$pid] = $projectsById[$pid];
            }
        }

        // Build members array
        $members = [];
        foreach ($users as $user) {
            $uid       = $user->getId();
            $active    = $userActiveTasks[$uid] ?? 0;
            $done      = $userDoneTasks[$uid] ?? 0;
            $projs     = array_values($userProjects[$uid] ?? []);
            $members[] = [
                'id'          => $uid,
                'firstname'   => $user->getFirstname(),
                'lastname'    => $user->getLastname(),
                'email'       => $user->getEmail(),
                'roleName'    => $user->getRoleName() ?? 'Membre',
                'jobtitle'    => $user->getJobtitle(),
                'fieldofwork' => $user->getFieldofwork(),
                'activeTasks' => $active,
                'doneTasks'   => $done,
                'projects'    => $projs,
            ];
        }

        // Team-level stats
        $totalMembers     = count($members);
        $totalActiveTasks = (int) array_sum($userActiveTasks);
        $totalDoneTasks   = (int) array_sum($userDoneTasks);
        $totalTasks       = $totalActiveTasks + $totalDoneTasks;
        $completionRate   = $totalTasks > 0 ? round($totalDoneTasks / $totalTasks * 100) : 0;

        // Top performers (top 3 by done tasks)
        $topPerformers = $members;
        usort($topPerformers, fn ($a, $b) => $b['doneTasks'] - $a['doneTasks']);
        $topPerformers = array_slice($topPerformers, 0, 3);

        // Role distribution
        $roleDistribution = [];
        foreach ($members as $m) {
            $role = $m['roleName'] ?: 'Membre';
            $roleDistribution[$role] = ($roleDistribution[$role] ?? 0) + 1;
        }
        arsort($roleDistribution);
        $maxRoleCount = max(array_values($roleDistribution) ?: [1]);

        // Active absences (users absent today)
        $activeAbsenceUserIds = [];
        try {
            $absenceRepo    = new AbsenceRepository();
            $activeAbsences = $absenceRepo->findActiveTodayUserIds();
            foreach ($activeAbsences as $userId) {
                $activeAbsenceUserIds[$userId] = true;
            }
        } catch (\Exception) {
            // Table may not exist yet (migration pending)
        }

        // Tag absent members
        foreach ($members as &$member) {
            $member['isAbsent'] = isset($activeAbsenceUserIds[$member['id']]);
        }
        unset($member);

        // Recent activity (last 5 informations)
        $recentActivity = $infoRepo->findRecentWithUser(5);

        return [
            'members'          => $members,
            'totalMembers'     => $totalMembers,
            'totalActiveTasks' => $totalActiveTasks,
            'totalDoneTasks'   => $totalDoneTasks,
            'completionRate'   => $completionRate,
            'topPerformers'    => $topPerformers,
            'roleDistribution' => $roleDistribution,
            'maxRoleCount'     => $maxRoleCount,
            'recentActivity'   => $recentActivity,
        ];
    }
}
