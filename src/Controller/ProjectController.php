<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Absence;
use Kentec\App\Model\Project;
use Kentec\App\Model\State;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\InputValidator;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class ProjectController extends AbstractController
{

    // Affichage des projets en mode "vue"
    #[OA\Get(
        path: '/projects',
        summary: 'Show projects list page',
        tags: ['Projects'],
        responses: [
            new OA\Response(response: 200, description: 'Projects page'),
        ]
    )]
    final public function index(): void
    {
        $projectRepo = new Repository(Project::class);
        $userRepo = new Repository(\Kentec\App\Model\User::class);
        $taskRepo = new Repository(\Kentec\App\Model\Task::class);
        $stateRepo = new Repository(\Kentec\App\Model\State::class);

        $currentUser = Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        // USER : seulement les projets sur lesquels il a au moins une tâche assignée
        if ($userRole === 'USER' && $currentUser) {
            $projects = $projectRepo->customQuery(
                'SELECT DISTINCT p.* FROM project p
                 INNER JOIN task t ON t.project_id = p.id
                 INNER JOIN usertaskREL ur ON ur.task_id = t.id
                 WHERE ur.user_id = :userId AND p.isactive = true
                 ORDER BY p.id DESC',
                ['userId' => $currentUser->getId()]
            );
        } else {
            $projects = $projectRepo->customQuery(
                'SELECT * FROM project WHERE isactive = true ORDER BY id DESC'
            );
        }

        // Récupération de tous les utilisateurs une seule fois
        $users = $userRepo->getAll();

        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user->getId()] = $user;
        }

        // Récupération des tâches et états
        $tasks = $taskRepo->getAll();

        $states = $stateRepo->getAll();

        // Construire un tableau des états par ID
        $statesById = [];
        if ($states) {
            foreach ($states as $state) {
                $statesById[$state->getId()] = $state;
            }
        }

        // Récupérer les assignations task->user via la table de liaison
        $assignments = $taskRepo->customQuery('SELECT user_id, task_id FROM usertaskREL');
        $taskDevelopers = [];
        foreach ($assignments as $assignment) {
            $taskDevelopers[$assignment['task_id']] = $assignment['user_id'];
        }

        // Ajout des infos calculées aux projets
        foreach ($projects as &$project) {
            // Normalisation des clés (DB lowercase vs Code camelCase)
            $project['beginDate'] = $project['beginDate'] ?? $project['begindate'] ?? null;
            $project['theoricalDeadLine'] = $project['theoricalDeadLine'] ?? $project['theoricaldeadline'] ?? null;
            $project['realDeadLine'] = $project['realDeadLine'] ?? $project['realdeadline'] ?? null;

            // Hydratation du chef de projet
            if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
                $manager = $usersById[$project['project_manager_id']];
                $project['manager_firstname'] = $manager->getFirstname();
                $project['manager_lastname'] = $manager->getLastname();
                $project['manager_object'] = $manager;
            }

            // Calculs basés sur les tâches
            $projectTasks = [];
            $completedTasksCount = 0;
            $participants = [];

            // Ajouter le manager comme participant de base
            if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
                $user = $usersById[$project['project_manager_id']];
                $participants[$project['project_manager_id']] = $user->getFirstname() . ' ' . $user->getLastname();
            }

            if ($tasks) {
                foreach ($tasks as $task) {
                    if ($task->getProjectId() == $project['id']) {
                        $projectTasks[] = $task;

                        // Vérifier le statut
                        $taskStateId = $task->getStateId();
                        if ($taskStateId && isset($statesById[$taskStateId])) {
                            $stateName = $statesById[$taskStateId]->getName();
                            if (false !== stripos($stateName, 'termin')
                                || false !== stripos($stateName, 'done')
                                || false !== stripos($stateName, 'clos')
                                || false !== stripos($stateName, 'fini')) {
                                ++$completedTasksCount;
                            }
                        }

                        // Ajouter le développeur assigné comme participant (via usertaskREL)
                        $devId = $taskDevelopers[$task->getId()] ?? null;
                        if ($devId && isset($usersById[$devId])) {
                            $user = $usersById[$devId];
                            $participants[$devId] = $user->getFirstname() . ' ' . $user->getLastname();
                        }
                    }
                }
            }

            $totalTasks = count($projectTasks);
            $project['task_count'] = $totalTasks;
            $project['progress'] = $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100) : 0;
            $project['participants_count'] = count($participants);
            $project['participants_list'] = array_values($participants);

            // Nom du statut du projet
            $projectStateId = $project['state_id'] ?? null;
            if ($projectStateId && isset($statesById[$projectStateId])) {
                $project['state_name'] = $statesById[$projectStateId]->getName();
            } else {
                $project['state_name'] = 'En attente';
            }
        }
        unset($project);

        // Comptage des projets par état (pour les onglets de filtre)
        $stateCounts = [];
        foreach ($statesById as $sid => $state) {
            $stateCounts[$sid] = 0;
        }
        $upcomingDeadlines = 0;
        $now      = new \DateTime();
        $nextWeek = (new \DateTime())->modify('+7 days');
        foreach ($projects as $project) {
            $sid = $project['state_id'] ?? null;
            if ($sid && isset($stateCounts[$sid])) {
                ++$stateCounts[$sid];
            }
            $deadline = $project['theoricalDeadLine'] ?? null;
            if ($deadline) {
                try {
                    $dl = new \DateTime($deadline);
                    if ($dl > $now && $dl <= $nextWeek) {
                        ++$upcomingDeadlines;
                    }
                } catch (\Exception $e) {
                    // date invalide, on ignore
                }
            }
        }

        // Comptage des projets par statut
        $statusStats = [
            'en_attente' => 0,
            'en_cours'   => 0,
            'termine'    => 0,
            'retarde'    => 0,
            'annule'     => 0,
        ];

        foreach ($projects as $project) {
            $stateId = $project['state_id'] ?? null;
            if ($stateId && isset($statesById[$stateId])) {
                $stateName = strtolower($statesById[$stateId]->getName());
                if (str_contains($stateName, 'annul')) {
                    ++$statusStats['annule'];
                } elseif (str_contains($stateName, 'retard')) {
                    ++$statusStats['retarde'];
                } elseif (str_contains($stateName, 'termin') || str_contains($stateName, 'done') || str_contains($stateName, 'fini')) {
                    ++$statusStats['termine'];
                } elseif (str_contains($stateName, 'cours') || str_contains($stateName, 'progress')) {
                    ++$statusStats['en_cours'];
                } else {
                    ++$statusStats['en_attente'];
                }
            } else {
                ++$statusStats['en_attente'];
            }
        }

        $this->render('project/project.php', [
            'pageTitle'         => 'Projets',
            'projects'          => $projects,
            'userRole'          => $userRole,
            'statusStats'       => $statusStats,
            'states'            => $statesById,
            'stateCounts'       => $stateCounts,
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }

    // Affichage des détails d'un projet
    #[OA\Get(
        path: '/project/{id}',
        summary: 'Show project details page',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project details page'),
            new OA\Response(response: 302, description: 'Redirect to projects list if not found'),
        ]
    )]
    final public function show(string $id): void
    {
        if (!InputValidator::validateUuid($id)) {
            header('Location: /projects');
            exit;
        }

        $currentUser = Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';
        $canCreate   = in_array($userRole, ['ADMIN', 'CDP', 'PDG'], true);
        $canDelete   = in_array($userRole, ['ADMIN', 'PDG'], true);

        $projectRepo = new Repository(Project::class);
        $userRepo    = new Repository(\Kentec\App\Model\User::class);
        $taskRepo    = new Repository(\Kentec\App\Model\Task::class);
        $stateRepo   = new Repository(\Kentec\App\Model\State::class);

        // Récupérer le projet actif
        $projects = $projectRepo->customQuery(
            'SELECT * FROM project WHERE id = :id AND isactive = true',
            ['id' => $id]
        );

        if (empty($projects)) {
            header('Location: /projects');
            exit;
        }

        $project = $projects[0];

        // Contrôle d'accès : USER ne peut voir que les projets sur lesquels il a une tâche
        if ($userRole === 'USER' && $currentUser) {
            $assigned = $taskRepo->customQuery(
                'SELECT 1 FROM task t
                 INNER JOIN usertaskREL ur ON ur.task_id = t.id
                 WHERE t.project_id = :projectId AND ur.user_id = :userId
                 LIMIT 1',
                ['projectId' => $id, 'userId' => $currentUser->getId()]
            );
            if (empty($assigned)) {
                header('Location: /projects');
                exit;
            }
        }

        // Normalisation des clés
        $project['beginDate'] = $project['beginDate'] ?? $project['begindate'] ?? null;
        $project['theoricalDeadLine'] = $project['theoricalDeadLine'] ?? $project['theoricaldeadline'] ?? null;
        $project['realDeadLine'] = $project['realDeadLine'] ?? $project['realdeadline'] ?? null;

        // Récupérer les utilisateurs
        $users = $userRepo->getAll();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user->getId()] = $user;
        }

        // Récupérer les états
        $states = $stateRepo->getAll();
        $statesById = [];
        $completedStateId = null;
        if ($states) {
            foreach ($states as $state) {
                $statesById[$state->getId()] = $state;
                if (false !== stripos($state->getName(), 'termin')
                    || false !== stripos($state->getName(), 'done')
                    || false !== stripos($state->getName(), 'clos')
                    || false !== stripos($state->getName(), 'fini')) {
                    $completedStateId = $state->getId();
                }
            }
        }

        // Hydratation manager
        if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
            $project['manager_object'] = $usersById[$project['project_manager_id']];
        }

        // Récupérer les tâches du projet
        $tasks = $taskRepo->getByAttributes(['project_id' => $id]);
        if (!is_array($tasks)) {
            $tasks = $tasks ? [$tasks] : [];
        }

        // Récupérer les assignations task->user via la table de liaison
        $assignments = $taskRepo->customQuery('SELECT user_id, task_id FROM usertaskREL');
        $taskDevelopers = [];
        foreach ($assignments as $assignment) {
            $taskDevelopers[$assignment['task_id']] = $assignment['user_id'];
        }

        // Calculs Statistiques
        $stats = [
            'total' => count($tasks),
            'completed' => 0,
            'in_progress' => 0,
            'todo' => 0,
            'progress' => 0,
        ];

        $absenceRepo   = new Repository(Absence::class);
        $absenceRows   = $absenceRepo->customQuery(
            "SELECT user_id, startdate, enddate FROM absence WHERE CURRENT_DATE BETWEEN startdate AND enddate"
        ) ?? [];
        $absentUserIds = array_column($absenceRows, 'user_id');
        $absenceByUser = [];
        foreach ($absenceRows as $row) {
            $absenceByUser[$row['user_id']] = $row;
        }

        $team = [];
        // Ajouter le manager à l'équipe
        if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
            $userId = $project['project_manager_id'];
            $team[$userId] = [
                'user'         => $usersById[$userId],
                'role'         => 'Chef de projet',
                'active_tasks' => 0,
                'is_absent'    => in_array($userId, $absentUserIds, true),
                'absence'      => $absenceByUser[$userId] ?? null,
            ];
        }

        foreach ($tasks as $task) {
            $stateId = $task->getStateId();

            // Stats par état
            $stateName = isset($statesById[$stateId]) ? $statesById[$stateId]->getName() : '';

            if ($stateId == $completedStateId) {
                ++$stats['completed'];
            } elseif (false !== stripos($stateName, 'todo')
                      || false !== stripos($stateName, 'faire')
                      || false !== stripos($stateName, 'attente')
                      || false !== stripos($stateName, 'backlog')) {
                ++$stats['todo'];
            } else {
                ++$stats['in_progress'];
            }

            // Membres de l'équipe via usertaskREL
            $devId = $taskDevelopers[$task->getId()] ?? null;
            if ($devId && isset($usersById[$devId])) {
                if (!isset($team[$devId])) {
                    $team[$devId] = [
                        'user'         => $usersById[$devId],
                        'role'         => 'Développeur',
                        'active_tasks' => 0,
                        'is_absent'    => in_array($devId, $absentUserIds, true),
                        'absence'      => $absenceByUser[$devId] ?? null,
                    ];
                }
                $isCompleted = (false !== stripos($stateName, 'termin')
                                || false !== stripos($stateName, 'done')
                                || false !== stripos($stateName, 'clos')
                                || false !== stripos($stateName, 'fini'));
                if (!$isCompleted) {
                    ++$team[$devId]['active_tasks'];
                }
            }
        }

        $stats['progress'] = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

        // Effort totals
        $effortRequired = 0.0;
        $effortMade     = 0.0;
        foreach ($tasks as $task) {
            $effortRequired += (float) ($task->getEffortrequired() ?? 0);
            $effortMade     += (float) ($task->getEffortmade()     ?? 0);
        }
        $stats['effort_required'] = round($effortRequired, 2);
        $stats['effort_made']     = round($effortMade, 2);

        $this->render('project/details.php', [
            'project'        => $project,
            'tasks'          => $tasks,
            'stats'          => $stats,
            'team'           => $team,
            'states'         => $statesById,
            'taskDevelopers' => $taskDevelopers,
            'userRole'       => $userRole,
            'canCreate'      => $canCreate,
            'canDelete'      => $canDelete,
            'allUsers'       => array_values($usersById),
        ]);
    }

    // API - Récupérer les projets en JSON
    #[OA\Get(
        path: '/api/projects',
        summary: 'Get all projects (JSON)',
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'projects', type: 'array', items: new OA\Items(ref: '#/components/schemas/Project')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiProjects(): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('La méthode HTTP doit être GET', 405);
            return;
        }

        try {
            $projectRepo = new Repository(Project::class);
            $userRepo    = new Repository(\Kentec\App\Model\User::class);

            $currentUser = Security::getUser();
            $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

            // USER : seulement les projets où il a au moins une tâche assignée
            if ($userRole === 'USER' && $currentUser) {
                $projects = $projectRepo->customQuery(
                    'SELECT DISTINCT p.* FROM project p
                     INNER JOIN task t ON t.project_id = p.id
                     INNER JOIN usertaskREL ur ON ur.task_id = t.id
                     WHERE ur.user_id = :userId AND p.isactive = true
                     ORDER BY p.id DESC',
                    ['userId' => $currentUser->getId()]
                );
            } else {
                $projects = $projectRepo->customQuery(
                    'SELECT * FROM project WHERE isactive = true ORDER BY id DESC'
                );
            }

            $users = $userRepo->getAll();
            $usersById = [];
            foreach ($users as $user) {
                $usersById[$user->getId()] = $user;
            }

            foreach ($projects as &$project) {
                // Normalisation des clés
                $project['beginDate'] = $project['beginDate'] ?? $project['begindate'] ?? null;
                $project['theoricalDeadLine'] = $project['theoricalDeadLine'] ?? $project['theoricaldeadline'] ?? null;
                $project['realDeadLine'] = $project['realDeadLine'] ?? $project['realdeadline'] ?? null;

                if (!empty($project['project_manager_id']) && isset($usersById[$project['project_manager_id']])) {
                    $manager = $usersById[$project['project_manager_id']];
                    $project['manager_firstname'] = $manager->getFirstname();
                    $project['manager_lastname'] = $manager->getLastname();
                    $project['manager_email'] = $manager->getEmail();
                }
            }

            $this->jsonSuccess(['projects' => $projects ?? []]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/project/{projectId}',
        summary: 'Get project by ID (JSON)',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'project', ref: '#/components/schemas/Project'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    final public function getApiProject(string $projectId): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('La méthode HTTP doit être GET', 405);
            return;
        }

        try {
            if (!InputValidator::validateUuid($projectId)) {
                $this->jsonError('Invalid UUID format', 400);
                return;
            }

            $taskRepo    = new Repository(\Kentec\App\Model\Task::class);
            $projectRepo = new Repository(Project::class);

            // USER : vérifier qu'il a au moins une tâche sur ce projet
            $currentUser = Security::getUser();
            $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

            if ($userRole === 'USER' && $currentUser) {
                $assigned = $taskRepo->customQuery(
                    'SELECT 1 FROM task t
                     INNER JOIN usertaskREL ur ON ur.task_id = t.id
                     WHERE t.project_id = :projectId AND ur.user_id = :userId
                     LIMIT 1',
                    ['projectId' => $projectId, 'userId' => $currentUser->getId()]
                );
                if (empty($assigned)) {
                    $this->jsonError('Accès non autorisé', 403);
                    return;
                }
            }

            // Requête enrichie avec jointures
            $rows = $projectRepo->customQuery(
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

            if (empty($rows)) {
                $this->jsonError('Projet introuvable', 404);
                return;
            }

            $project = Project::fromDatabaseArray($rows[0]);

            // Calcul d'effort via méthode dédiée
            $effort = $this->calculerEffortProjet($projectId);

            $this->jsonSuccess([
                'project'            => $project,
                'totalEffortRequired' => $effort['totalEffortRequired'],
                'totalEffortMade'     => $effort['totalEffortMade'],
                'totalTasks'          => $effort['totalTasks'],
            ]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Calcule les totaux d'effort pour un projet.
     * Utilise COALESCE pour retourner 0 si aucune tâche n'est associée.
     */
    private function calculerEffortProjet(string $projectId): array
    {
        $taskRepo = new Repository(\Kentec\App\Model\Task::class);

        $rows = $taskRepo->customQuery(
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

    final public function dynamicalProjects(): void
    {
        $projectRepo = new Repository(Project::class);
        $projects = $projectRepo->customQuery('SELECT * FROM project WHERE isactive = true');
        $this->render('project/dynamicalProjects.php', ['projects' => $projects]);
    }

    #[OA\Delete(
        path: '/api/delete/project/{projectId}',
        summary: 'Soft-delete project (isactive = false)',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project soft-deleted'),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    final public function deleteApiProject(string $projectId): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('DELETE' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('La méthode HTTP doit être DELETE', 405);
            return;
        }

        try {
            if (!InputValidator::validateUuid($projectId)) {
                $this->jsonError('Invalid Project ID (must be a valid UUID)', 400);
                return;
            }

            $currentUser = Security::getUser();

            $projectRepo = new Repository(Project::class);
            $project = $projectRepo->getById($projectId);

            if (!$project) {
                $this->jsonError('Project not found', 404);
                return;
            }

            // Soft delete : isactive = false (jamais de DELETE physique)
            $updatedby = $currentUser ? $currentUser->getId() : null;
            $projectRepo->customQuery(
                'UPDATE project
                 SET isactive = false,
                     updatedat = NOW(),
                     updatedby = :updatedby
                 WHERE id = :id',
                ['updatedby' => $updatedby, 'id' => $projectId]
            );

            $this->jsonSuccess(['message' => 'Projet archivé avec succès']);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/add/project',
        summary: 'Add new project (JSON)',
        tags: ['Projects'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'beginDate', 'theoreticalDeadline'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'projectManagerId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'beginDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'theoreticalDeadline', type: 'string', format: 'date'),
                    new OA\Property(property: 'realDeadline', type: 'string', format: 'date'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Project created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function addApiProject(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('La méthode HTTP doit être POST', 405);
            return;
        }

        try {
            $inputData = json_decode(file_get_contents('php://input'), true);

            if (!$inputData) {
                $this->jsonError('No data received', 400);
                return;
            }

            if (empty($inputData['name'])) {
                $this->jsonError('Le nom du projet est obligatoire', 400);
                return;
            }

            if (empty($inputData['beginDate'])) {
                $this->jsonError('La date de début est obligatoire', 400);
                return;
            }

            if (empty($inputData['theoreticalDeadline'])) {
                $this->jsonError("L'échéance théorique est obligatoire.", 400);
                return;
            }

            $currentUser = Security::getUser();

            $projectRepo = new Repository(Project::class);
            $stateRepo   = new Repository(State::class);

            $project = new Project();
            $project->setName($inputData['name']);
            $project->setDescription($inputData['description'] ?? null);
            $project->setClientId($inputData['clientId'] ?? null);

            // Attribution automatique du chef de projet (utilisateur connecté si non fourni)
            $managerId = $inputData['projectManagerId'] ?? null;
            if (empty($managerId) && $currentUser) {
                $managerId = $currentUser->getId();
            }
            $project->setProjectManagerId($managerId);

            $project->setBeginDate(new \DateTime($inputData['beginDate']));
            $project->setTheoreticalDeadline(new \DateTime($inputData['theoreticalDeadline']));

            if (!empty($inputData['realDeadline'])) {
                $project->setRealDeadline(new \DateTime($inputData['realDeadline']));
            }

            // Attribution automatique du statut "En attente"
            $statesEnAttente = $stateRepo->customQuery(
                "SELECT * FROM state WHERE LOWER(name) LIKE '%attente%' LIMIT 1"
            );
            if (!empty($statesEnAttente)) {
                $project->setStateId($statesEnAttente[0]['id']);
            }

            // Champs d'audit
            if ($currentUser) {
                $project->setCreatedby($currentUser->getId());
                $project->setUpdatedby($currentUser->getId());
            }

            $projectRepo->insert($project);

            $this->jsonSuccess(['project' => $project->toArray()]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    #[OA\Put(
        path: '/api/edit/project/{projectId}',
        summary: 'Edit project (JSON)',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'projectManagerId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'stateId', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'beginDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'theoreticalDeadline', type: 'string', format: 'date'),
                    new OA\Property(property: 'realDeadline', type: 'string', format: 'date'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Project updated'),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    final public function editApiProject(string $projectId): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
            return;
        }

        if ('PUT' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('La méthode HTTP doit être PUT', 405);
            return;
        }

        try {
            if (!InputValidator::validateUuid($projectId)) {
                $this->jsonError('Invalid UUID format', 400);
                return;
            }

            $inputData = json_decode(file_get_contents('php://input'), true);

            if (!$inputData) {
                $this->jsonError('No data received', 400);
                return;
            }

            $currentUser = Security::getUser();
            $projectRepo = new Repository(Project::class);
            $stateRepo   = new Repository(State::class);
            $taskRepo    = new Repository(\Kentec\App\Model\Task::class);

            $project = $projectRepo->getById($projectId);

            if (!$project) {
                $this->jsonError('Project not found', 404);
                return;
            }

            $previousStateId = $project->getStateId();

            if (isset($inputData['name'])) {
                $project->setName($inputData['name']);
            }
            if (isset($inputData['description'])) {
                $project->setDescription($inputData['description']);
            }
            if (isset($inputData['clientId'])) {
                $project->setClientId($inputData['clientId']);
            }
            if (isset($inputData['projectManagerId'])) {
                $project->setProjectManagerId($inputData['projectManagerId']);
            }

            // Validation du stateId si fourni
            if (isset($inputData['stateId']) && $inputData['stateId'] !== null) {
                $stateExists = $stateRepo->getById($inputData['stateId']);
                if (!$stateExists) {
                    $this->jsonError('Statut invalide : cet identifiant ne correspond à aucun statut existant.', 400);
                    return;
                }
                $project->setStateId($inputData['stateId']);
            }

            if (isset($inputData['beginDate'])) {
                $project->setBeginDate($inputData['beginDate'] ? new \DateTime($inputData['beginDate']) : null);
            }
            if (isset($inputData['theoreticalDeadline'])) {
                $project->setTheoreticalDeadline($inputData['theoreticalDeadline'] ? new \DateTime($inputData['theoreticalDeadline']) : null);
            }
            if (isset($inputData['realDeadline'])) {
                $project->setRealDeadline($inputData['realDeadline'] ? new \DateTime($inputData['realDeadline']) : null);
            }

            // Champs d'audit
            if ($currentUser) {
                $project->setUpdatedby($currentUser->getId());
            }

            // Mise à jour en base avec updatedat = NOW()
            $projectRepo->customQuery(
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

            // Mise à jour du statut des tâches (sauf si statut = retardé)
            $newStateId = $project->getStateId();
            if ($newStateId && $newStateId !== $previousStateId) {
                $newState = $stateRepo->getById($newStateId);
                $isLate = $newState && stripos($newState->getName(), 'retard') !== false;

                if (!$isLate) {
                    $taskRepo->customQuery(
                        'UPDATE task SET state_id = :stateId WHERE project_id = :projectId',
                        ['stateId' => $newStateId, 'projectId' => $projectId]
                    );
                }
            }

            $this->jsonSuccess(['project' => $project->toArray()]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
}
