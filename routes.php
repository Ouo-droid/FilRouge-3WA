<?php

declare(strict_types=1);

const ROUTES = [
    '/' => [
        'CONTROLLER'   => 'HomeController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],

    // ----------------------- AUTHENTIFICATION ---------------------------
    '/login' => [
        'CONTROLLER'   => 'AuthController',
        'METHOD'       => 'login',
        'HTTP_METHODS' => ['GET', 'POST'],
    ],
    '/logout' => [
        'CONTROLLER'   => 'AuthController',
        'METHOD'       => 'logout',
        'HTTP_METHODS' => ['GET'],
    ],
    '/register' => [
        'CONTROLLER'   => 'AuthController',
        'METHOD'       => 'register',
        'HTTP_METHODS' => ['GET', 'POST'],
        'AUTH'         => ['ADMIN', 'PDG'],
    ],

    // ----------------------- UTILISATEURS ---------------------------
    '/users' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],
    '/team' => [
        'CONTROLLER'   => 'TeamController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/team/stats' => [
        'CONTROLLER'   => 'TeamController',
        'METHOD'       => 'getApiStats',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/roles' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'getApiRoles',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/users' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'getApiUsers',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/user/{userId}' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'getApiUser',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/add/user' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'addApiUser',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],
    '/api/edit/user/{userId}' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'editApiUser',
        'HTTP_METHODS' => 'PATCH',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],
    '/api/delete/user/{userId}' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'deleteApiUser',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],
    '/api/change-password' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'changePassword',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => true,
    ],
    '/api/delete/my-account' => [
        'CONTROLLER'   => 'UsersController',
        'METHOD'       => 'deleteMyAccount',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => true,
    ],

    // ----------------------- PROJETS ---------------------------
    '/projects' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/projects' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'getApiProjects',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/project/{id}' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'show',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/project/{projectId}' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'getApiProject',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/add/project' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'addApiProject',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/edit/project/{projectId}' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'editApiProject',
        'HTTP_METHODS' => 'PUT',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/delete/project/{projectId}' => [
        'CONTROLLER'   => 'ProjectController',
        'METHOD'       => 'deleteApiProject',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],

    // ----------------------- TACHES ---------------------------
    '/tasks' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/tasks' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'getApiTasks',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/task/{taskId}' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'getApiTask',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/states' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'getApiStates',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/add/task' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'addApiTask',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/edit/task/{taskId}' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'editApiTask',
        'HTTP_METHODS' => 'PUT',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG', 'USER'],
    ],
    '/api/delete/task/{taskId}' => [
        'CONTROLLER'   => 'TaskController',
        'METHOD'       => 'deleteApiTask',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],

    // ----------------------- CLIENT ---------------------------
    '/clients' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/clients' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'getApiClients',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/clients/search' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'searchApiClients',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/client/{numSIRET}' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'getApiClient',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/add/client' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'addApiClient',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/edit/client/{numSIRET}' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'editApiClient',
        'HTTP_METHODS' => 'PUT',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],
    '/api/delete/client/{numSIRET}' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'deleteApiClient',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],
    '/api/siren-lookup' => [
        'CONTROLLER'   => 'ClientController',
        'METHOD'       => 'sirenLookup',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'CDP', 'PDG'],
    ],

    // ----------------------- RECHERCHES ---------------------------
    '/search' => [
        'CONTROLLER'   => 'SearchController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/search/global' => [
        'CONTROLLER'   => 'SearchController',
        'METHOD'       => 'globalSearch',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/search/autocomplete' => [
        'CONTROLLER'   => 'SearchController',
        'METHOD'       => 'autocomplete',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/settings' => [
        'CONTROLLER'   => 'SettingsController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],

    // ----------------------- INFORMATION ---------------------------
    '/api/informations' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'getApiInformations',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/information/{id}' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'getApiInformation',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/user/{userId}/informations' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'getApiUserInformations',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/add/information' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'addApiInformation',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => true,
    ],
    '/api/edit/information/{id}' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'editApiInformation',
        'HTTP_METHODS' => 'PUT',
        'AUTH'         => true,
    ],
    '/api/delete/information/{id}' => [
        'CONTROLLER'   => 'InformationController',
        'METHOD'       => 'deleteApiInformation',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG'],
    ],

    // ----------------------- ABSENCES ---------------------------
    '/api/absences' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'getAll',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => ['ADMIN', 'PDG', 'CDP'],
    ],
    '/api/absences/active' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'getActive',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/absences/user/{userId}' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'getByUser',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api/add/absence' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'add',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['ADMIN', 'PDG', 'CDP'],
    ],
    '/api/request/my-absence' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'requestOwn',
        'HTTP_METHODS' => 'POST',
        'AUTH'         => ['USER', 'CDP', 'ADMIN', 'PDG'],
    ],
    '/api/delete/absence/{id}' => [
        'CONTROLLER'   => 'AbsenceController',
        'METHOD'       => 'delete',
        'HTTP_METHODS' => 'DELETE',
        'AUTH'         => ['ADMIN', 'PDG', 'CDP'],
    ],

    // ----------------------- SWAGGER ---------------------------
    '/swagger' => [
        'CONTROLLER'   => 'SwaggerController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
    ],
    '/history' => [
        'CONTROLLER'   => 'HistoryController',
        'METHOD'       => 'index',
        'HTTP_METHODS' => 'GET',
        'AUTH'         => true,
    ],
    '/api-docs' => [
        'CONTROLLER'   => 'SwaggerController',
        'METHOD'       => 'getApiDocs',
        'HTTP_METHODS' => 'GET',
    ],
];
