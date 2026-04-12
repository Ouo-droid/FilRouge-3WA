<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Task;
use Kentec\App\Repository\AbsenceRepository;
use Kentec\App\Repository\TaskRepository;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\InputValidator;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class TaskController extends AbstractController
{

    // Affichage des tasks en mode "vue"
    #[OA\Get(
        path: '/tasks',
        summary: 'Show tasks list page',
        tags: ['Tasks'],
        responses: [
            new OA\Response(response: 200, description: 'Tasks page'),
        ]
    )]
    final public function index(): void
    {
        $taskRepo    = new TaskRepository();
        $userRepo    = new Repository(\Kentec\App\Model\User::class);
        $projectRepo = new Repository(\Kentec\App\Model\Project::class);
        $stateRepo   = new Repository(\Kentec\App\Model\State::class);

        // Collaborateur : seulement ses tâches assignées
        $currentUser = Security::getUser();
        $role        = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        if ($role === 'USER' && $currentUser) {
            $tasks = $taskRepo->customQuery(
                'SELECT t.* FROM task t
                 INNER JOIN usertaskREL ur ON ur.task_id = t.id
                 WHERE ur.user_id = :userId AND t.isactive = true
                 ORDER BY t.id DESC',
                ['userId' => $currentUser->getId()]
            );
        } else {
            $tasks = $taskRepo->customQuery('SELECT * FROM task WHERE isactive = true ORDER BY id DESC');
        }

        // Récupération de tous les utilisateurs une seule fois
        $users     = $userRepo->getAll() ?? [];
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user->getId()] = $user;
        }

        // Récupération de tous les projets une seule fois
        $projects     = $projectRepo->getAll() ?? [];
        $projectsById = [];
        foreach ($projects as $project) {
            $projectsById[$project->getId()] = $project;
        }

        // Récupération de tous les states une seule fois
        $states     = $stateRepo->getAll() ?? [];
        $statesById = [];
        foreach ($states as $state) {
            $statesById[$state->getId()] = $state->getName();
        }

        // Récupération des assignations user/task via la table de liaison
        $assignments    = $taskRepo->findAllUserAssignments();
        $taskDevelopers = [];
        foreach ($assignments as $assignment) {
            $taskDevelopers[$assignment['task_id']] = $assignment['user_id'];
        }

        // Absences actives aujourd'hui
        $absenceRepo   = new AbsenceRepository();
        $absenceRows   = $absenceRepo->findActiveTodayWithDates();
        $absentUserIds = array_column($absenceRows, 'user_id');
        $absenceByUser = array_column($absenceRows, null, 'user_id');

        // Ajout des informations liées aux tasks
        $tasks = $tasks ?: [];
        foreach ($tasks as &$task) {
            $devId = $taskDevelopers[$task['id']] ?? null;
            if ($devId && isset($usersById[$devId])) {
                $developer = $usersById[$devId];
                $task['developer_id']        = $devId;
                $task['developer_firstname'] = $developer->getFirstname();
                $task['developer_lastname']  = $developer->getLastname();
                $task['developer_email']     = $developer->getEmail();
                $task['dev_absent']          = in_array($devId, $absentUserIds, true);
                $task['dev_absence_start']   = $absenceByUser[$devId]['startdate'] ?? null;
                $task['dev_absence_end']     = $absenceByUser[$devId]['enddate'] ?? null;
            } else {
                $task['dev_absent'] = false;
            }

            if (!empty($task['project_id']) && isset($projectsById[$task['project_id']])) {
                $project = $projectsById[$task['project_id']];
                $task['project_name'] = $project->getName();
            }

            if (!empty($task['state_id']) && isset($statesById[$task['state_id']])) {
                $task['state_name'] = $statesById[$task['state_id']];
            }

            // Normalisation des clés de date pour la vue (qui attend du camelCase)
            if (isset($task['begindate'])) {
                $task['beginDate'] = $task['begindate'];
            }
            if (isset($task['theoricalenddate'])) {
                $task['theoricalEndDate'] = $task['theoricalenddate'];
            }
            if (isset($task['realenddate'])) {
                $task['realEndDate'] = $task['realenddate'];
            }
        }

        $this->render('task/tasks.php', [
            'pageTitle' => 'Tâches',
            'tasks'     => $tasks,
            'states'    => $states,
            'projects'  => $projects,
            'users'     => array_values($usersById),
            'userRole'  => $role,
        ]);
    }

    // API - Récupérer les tasks en JSON
    #[OA\Get(
        path: '/api/tasks',
        summary: 'Get all tasks (JSON)',
        tags: ['Tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'tasks', type: 'array', items: new OA\Items(ref: '#/components/schemas/Task')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiTasks(): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $taskRepo    = new TaskRepository();
                $userRepo    = new Repository(\Kentec\App\Model\User::class);
                $projectRepo = new Repository(\Kentec\App\Model\Project::class);

                $currentUser = Security::getUser();
                $role        = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

                // USER : seulement ses tâches assignées
                if ($role === 'USER' && $currentUser) {
                    $tasks = $taskRepo->customQuery(
                        'SELECT t.* FROM task t
                         INNER JOIN usertaskREL ur ON ur.task_id = t.id
                         WHERE ur.user_id = :userId AND t.isactive = true
                         ORDER BY t.id DESC',
                        ['userId' => $currentUser->getId()]
                    );
                } else {
                    $tasks = $taskRepo->customQuery('SELECT * FROM task WHERE isactive = true ORDER BY id DESC');
                }

                // Récupération de tous les utilisateurs une seule fois
                $users     = $userRepo->getAll() ?? [];
                $usersById = [];
                foreach ($users as $user) {
                    $usersById[$user->getId()] = $user;
                }

                // Récupération de tous les projets une seule fois
                $projects     = $projectRepo->getAll() ?? [];
                $projectsById = [];
                foreach ($projects as $project) {
                    $projectsById[$project->getId()] = $project;
                }

                // Récupération des assignations user/task via la table de liaison
                $assignments    = $taskRepo->findAllUserAssignments();
                $taskDevelopers = [];
                foreach ($assignments as $assignment) {
                    $taskDevelopers[$assignment['task_id']] = $assignment['user_id'];
                }

                // Ajout des informations liées aux tasks
                foreach ($tasks as &$task) {
                    $devId = $taskDevelopers[$task['id']] ?? null;
                    if ($devId && isset($usersById[$devId])) {
                        $developer = $usersById[$devId];
                        $task['developer_firstname'] = $developer->getFirstname();
                        $task['developer_lastname'] = $developer->getLastname();
                        $task['developer_email'] = $developer->getEmail();
                    }

                    if (!empty($task['project_id']) && isset($projectsById[$task['project_id']])) {
                        $project = $projectsById[$task['project_id']];
                        $task['project_name'] = $project->getName();
                    }
                }

                if (!$tasks) {
                    $this->json([
                        'tasks' => [],
                    ]);

                    return;
                }

                $this->json([
                    'tasks' => $tasks,
                ]);
            } catch (\Exception $e) {
                $this->json([
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être GET',
            ]);
        }
    }

    #[OA\Get(
        path: '/api/task/{taskId}',
        summary: 'Get task by ID (JSON)',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'taskId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'task', ref: '#/components/schemas/Task'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    final public function getApiTask(string $taskId): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                if (!InputValidator::validateUuid($taskId)) {
                    throw new \Exception('Invalid UUID format');
                }

                $taskRepo = new TaskRepository();

                // USER : vérifier qu'il est bien assigné à cette tâche
                $currentUser = Security::getUser();
                $role        = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

                if ($role === 'USER' && $currentUser) {
                    if (!$taskRepo->isAssignedToUser($taskId, $currentUser->getId())) {
                        $this->jsonError('Accès non autorisé', 403);
                        return;
                    }
                }

                $task = $taskRepo->getById($taskId);

                if (!$task) {
                    throw new \Exception('No task found');
                }
                $this->json([
                    'task' => $task->toArray(),
                ]);
            } catch (\Exception $e) {
                $this->json([
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être un GET',
            ]);
        }
    }

    #[OA\Delete(
        path: '/api/delete/task/{taskId}',
        summary: 'Delete task',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'taskId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task deleted'),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    final public function deleteApiTask(string $taskId): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('DELETE' === $_SERVER['REQUEST_METHOD']) {
            try {
                // Validate taskId
                if ('null' === $taskId || '' === $taskId || !InputValidator::validateUuid($taskId)) {
                    $this->json([
                        'success' => false,
                        'error' => 'Invalid task ID',
                    ]);

                    return;
                }

                $taskRepo = new TaskRepository();
                $task     = $taskRepo->getById($taskId);

                if (!$task) {
                    $this->json([
                        'success' => false,
                        'error' => 'Task not found',
                    ]);

                    return;
                }

                $currentUser = Security::getUser();

                // Archiver la tâche (isactive = false) au lieu de la supprimer physiquement
                $taskRepo->softDelete($taskId, $currentUser?->getId()); // updatedby updatedat = NOW()

                $this->jsonSuccess(['message' => 'Tâche archivée avec succès']);
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être DELETE',
            ]);
        }
    }

    #[OA\Post(
        path: '/api/add/task',
        summary: 'Add new task (JSON)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'effortRequired'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'format', type: 'string'),
                    new OA\Property(property: 'priority', type: 'string'),
                    new OA\Property(property: 'dificulty', type: 'string'),
                    new OA\Property(property: 'effortRequired', type: 'number', format: 'float'),
                    new OA\Property(property: 'projectId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'stateId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'beginDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'theoricalEndDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'realEndDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'developerId', type: 'string', format: 'uuid'),
                ]
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(response: 200, description: 'Task created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function addApiTask(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            try {
                // Récupération des données JSON
                $rawInput = file_get_contents('php://input');
                $inputData = json_decode($rawInput, true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                // Validation des champs obligatoires
                if (empty($inputData['name'])) {
                    throw new \Exception('Task name is required');
                }
                if (empty($inputData['type'])) {
                    throw new \Exception('Le type de la tâche est obligatoire.');
                }

                $taskRepo = new TaskRepository();
                $task     = new Task();

                // Assignation des valeurs
                $task->setName($inputData['name']);
                $task->setDescription($inputData['description'] ?? null);
                $task->setType($inputData['type'] ?? null);
                $task->setFormat($inputData['format'] ?? null);
                $task->setPriority($inputData['priority'] ?? null);
                $task->setDifficulty($inputData['dificulty'] ?? $inputData['difficulty'] ?? null);


                // Validate effortRequired
                if (!isset($inputData['effortRequired']) || '' === $inputData['effortRequired'] || null === $inputData['effortRequired']) {
                    throw new \Exception('Le champ effortRequired est obligatoire.');
                }
                $effort = floatval($inputData['effortRequired']);
                if ($effort <= 0 || $effort > 99.99) {
                    throw new \Exception("L'effort doit être compris entre 0.01 et 99.99 heures.");
                }
                $task->setEffortrequired($effort);

                // Validate Project ID UUID
                if (!empty($inputData['projectId'])) {
                    if (!InputValidator::validateUuid($inputData['projectId'])) {
                        throw new \Exception('Invalid Project ID (must be a valid UUID)');
                    }
                    $task->setProjectId($inputData['projectId']);
                }

                // Validate State ID UUID
                if (!empty($inputData['stateId'])) {
                    if (!InputValidator::validateUuid($inputData['stateId'])) {
                        throw new \Exception('Invalid State ID (must be a valid UUID)');
                    }
                    $task->setStateId($inputData['stateId']);
                }

                // Gestion des dates
                if (!empty($inputData['beginDate'])) {
                    $task->setBeginDate(new \DateTime($inputData['beginDate']));
                }
                if (empty($inputData['theoricalEndDate'])) {
                    throw new \Exception("L'échéance théorique est obligatoire.");
                }
                $task->setTheoreticalEndDate(new \DateTime($inputData['theoricalEndDate']));
                if (!empty($inputData['realEndDate'])) {
                    $task->setRealEndDate(new \DateTime($inputData['realEndDate']));
                }

                // Attribution automatique du statut "En attente"
                $projectRepoForState = new ProjectRepository();
                $defaultStateId = $projectRepoForState->findDefaultStateId();
                if ($defaultStateId !== null) {
                    $task->setStateId($defaultStateId);
                }

                // Sauvegarde avec récupération de l'ID généré
                $currentUser = Security::getUser();
                if ($currentUser) {
                    $task->setCreatedby($currentUser->getId());
                    $task->setUpdatedby($currentUser->getId());
                }
                $newTaskId = $taskRepo->insertAndReturnId($task);

                // Assignation du développeur
                if ($newTaskId && !empty($inputData['developerId']) && InputValidator::validateUuid($inputData['developerId'])) {
                    $taskRepo->deleteUserAssignment($newTaskId);
                    $taskRepo->insertUserAssignment($inputData['developerId'], $newTaskId);
                }

                $this->jsonSuccess([
                    'message' => 'Task created successfully',
                    'task' => $task->toArray(),
                ]);
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être POST',
            ]);
        }
    }

    #[OA\Put(
        path: '/api/edit/task/{taskId}',
        summary: 'Edit task (JSON)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'format', type: 'string'),
                    new OA\Property(property: 'priority', type: 'string'),
                    new OA\Property(property: 'dificulty', type: 'string'),
                    new OA\Property(property: 'effortRequired', type: 'number', format: 'float'),
                    new OA\Property(property: 'effortMade', type: 'number', format: 'float'),
                    new OA\Property(property: 'projectId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'stateId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'beginDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'theoricalEndDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'realEndDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'developerId', type: 'string', format: 'uuid'),
                ]
            )
        ),
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'taskId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task updated'),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    final public function editApiTask(string $taskId): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('PUT' === $_SERVER['REQUEST_METHOD']) {
            try {
                if (!InputValidator::validateUuid($taskId)) {
                    throw new \Exception('Invalid UUID format');
                }

                // Récupération des données JSON
                $rawInput = file_get_contents('php://input');
                $inputData = json_decode($rawInput, true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                $taskRepo = new TaskRepository();
                $task     = $taskRepo->getById($taskId);

                if (!$task) {
                    throw new \Exception('Task not found');
                }

                // Mise à jour des champs
                if (isset($inputData['name'])) {
                    $task->setName($inputData['name']);
                }
                if (isset($inputData['description'])) {
                    $task->setDescription($inputData['description']);
                }
                if (isset($inputData['type'])) {
                    $task->setType($inputData['type']);
                }
                if (isset($inputData['format'])) {
                    $task->setFormat($inputData['format']);
                }
                if (isset($inputData['priority'])) {
                    $task->setPriority($inputData['priority']);
                }
                if (isset($inputData['dificulty'])) {
                    $task->setDifficulty($inputData['dificulty'] ?? $inputData['difficulty']);
                }
                if (isset($inputData['effortRequired'])) {
                    $effort = floatval($inputData['effortRequired']);
                    if ($effort <= 0 || $effort > 99.99) {
                        throw new \Exception("L'effort doit être compris entre 0.01 et 99.99 heures.");
                    }
                    $task->setEffortrequired($effort);
                }
                if (isset($inputData['effortMade'])) {
                    $effortMade = floatval($inputData['effortMade']);
                    if ($effortMade <= 0) {
                        throw new \Exception("L'effort réel doit être supérieur à 0.");
                    }
                    $task->setEffortmade($effortMade);
                }
                if (!empty($inputData['projectId'])) {
                    if (!InputValidator::validateUuid($inputData['projectId'])) {
                        throw new \Exception('Invalid Project ID (must be a valid UUID)');
                    }
                    $task->setProjectId($inputData['projectId']);
                }
                if (!empty($inputData['stateId'])) {
                    if (!InputValidator::validateUuid($inputData['stateId'])) {
                        throw new \Exception('Invalid State ID (must be a valid UUID)');
                    }
                    $task->setStateId($inputData['stateId']);
                }

                // Gestion des dates
                if (isset($inputData['beginDate'])) {
                    $task->setBeginDate($inputData['beginDate'] ? new \DateTime($inputData['beginDate']) : null);
                }
                if (isset($inputData['theoricalEndDate'])) {
                    $task->setTheoreticalEndDate($inputData['theoricalEndDate'] ? new \DateTime($inputData['theoricalEndDate']) : null);
                }
                if (isset($inputData['realEndDate'])) {
                    $task->setRealEndDate($inputData['realEndDate'] ? new \DateTime($inputData['realEndDate']) : null);
                }

                // Sauvegarde
                $currentUser = Security::getUser();
                if ($currentUser) {
                    $task->setUpdatedby($currentUser->getId());
                }
                $taskRepo->updateTask($task, $taskId); // updatedat = NOW()

                // Assignation du développeur
                if (!empty($inputData['developerId']) && InputValidator::validateUuid($inputData['developerId'])) {
                    $taskRepo->deleteUserAssignment($taskId);
                    $taskRepo->insertUserAssignment($inputData['developerId'], $taskId);
                }

                $this->jsonSuccess([
                    'message' => 'Task updated successfully',
                    'task' => $task->toArray(),
                ]);
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être PUT',
            ]);
        }
    }

    #[OA\Get(
        path: '/api/states',
        summary: 'Get all task states (JSON)',
        tags: ['Tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'states', type: 'array', items: new OA\Items(ref: '#/components/schemas/State')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiStates(): void
    {
        try {
            $stateRepo = new Repository(\Kentec\App\Model\State::class);
            $states = $stateRepo->getAll();

            $statesArray = [];
            if ($states) {
                foreach ($states as $state) {
                    $statesArray[] = $state->toArray();
                }
            }

            $this->jsonSuccess([
                'states' => $statesArray,
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
