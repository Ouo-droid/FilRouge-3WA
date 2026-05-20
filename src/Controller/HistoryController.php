<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Repository\ProjectRepository;
use Kentec\App\Repository\TaskRepository;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\Security;

class HistoryController extends AbstractController
{
    final public function index(): void
    {
        $projectRepo = new ProjectRepository();
        $taskRepo    = new TaskRepository();
        $userRepo    = new Repository(\Kentec\App\Model\User::class);
        $stateRepo   = new Repository(\Kentec\App\Model\State::class);

        $currentUser = Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        // Récupération des projets archivés (isactive = false)
        if ($userRole === 'USER' && $currentUser) {
            $archivedProjects = $projectRepo->findArchivedByUserId($currentUser->getId());
            $archivedTasks    = $taskRepo->findArchivedByUserId($currentUser->getId());
        } else {
            $archivedProjects = $projectRepo->findAllArchived();
            $archivedTasks    = $taskRepo->findAllArchived();
        }

        // Hydratation basique pour l'affichage
        $users = $userRepo->getAll();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user->getId()] = $user;
        }

        $states = $stateRepo->getAll();
        $statesById = [];
        foreach ($states as $state) {
            $statesById[$state->getId()] = $state->getName();
        }

        foreach ($archivedProjects as &$project) {
            if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
                $manager = $usersById[$project['project_manager_id']];
                $project['manager_name'] = $manager->getFirstname() . ' ' . $manager->getLastname();
            }
            if (!empty($project['state_id']) && isset($statesById[$project['state_id']])) {
                $project['state_name'] = $statesById[$project['state_id']];
            }
        }
        unset($project);

        foreach ($archivedTasks as &$task) {
            if (!empty($task['state_id']) && isset($statesById[$task['state_id']])) {
                $task['state_name'] = $statesById[$task['state_id']];
            }
        }
        unset($task);

        $this->render('history/history.php', [
            'pageTitle'        => 'Historique',
            'archivedProjects' => $archivedProjects,
            'archivedTasks'    => $archivedTasks,
            'userRole'         => $userRole,
        ]);
    }
}
