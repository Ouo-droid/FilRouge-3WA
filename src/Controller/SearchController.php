<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\Kernel\Database\SearchableRepository;
use Kentec\Kernel\Http\AbstractController;
use OpenApi\Attributes as OA;

/**
 * Contrôleur de recherche générique
 */
class SearchController extends AbstractController
{
    /**
     * Configuration des entités recherchables, filtrées selon le rôle courant.
     * USER : projets et tâches uniquement (pas d'accès aux annuaires users/clients).
     */
    private function getSearchableEntities(): array
    {
        $currentUser = \Kentec\Kernel\Security\Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        $all = [
            'users' => [
                'model'        => \Kentec\App\Model\User::class,
                'columns'      => ['firstname', 'lastname', 'email'],
                'display'      => ['firstname', 'lastname', 'email'],
                'label'        => 'Utilisateurs',
                'default_sort' => 'id',
            ],
            'clients' => [
                'model'        => \Kentec\App\Model\Client::class,
                'columns'      => ['companyname', 'numsiret', 'contactfirstname', 'contactlastname'],
                'display'      => ['companyname', 'contactfirstname', 'contactlastname'],
                'label'        => 'Clients',
                'default_sort' => 'numsiret',
            ],
            'projects' => [
                'model'        => \Kentec\App\Model\Project::class,
                'columns'      => ['name', 'description'],
                'display'      => ['name'],
                'label'        => 'Projets',
                'default_sort' => 'id',
            ],
            'tasks' => [
                'model'        => \Kentec\App\Model\Task::class,
                'columns'      => ['name', 'description'],
                'display'      => ['name'],
                'label'        => 'Tâches',
                'default_sort' => 'id',
            ],
        ];

        if ($userRole === 'USER') {
            return array_intersect_key($all, array_flip(['projects', 'tasks']));
        }

        return $all;
    }

    /**
     * Recherche via barre de recherche
     * GET /search?q=terme&entity=users&page=1
     */
    #[OA\Get(
        path: '/search',
        summary: 'Search entities',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'entity', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['users', 'clients', 'projects', 'tasks'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'format', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['json'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results (HTML or JSON)'),
            new OA\Response(response: 400, description: 'Invalid entity type'),
        ]
    )]
    public function index()
    {
        $searchTerm         = $_GET['q'] ?? '';
        $page               = (int) ($_GET['page'] ?? 1);
        $perPage            = 20;
        $searchableEntities = $this->getSearchableEntities();
        $defaultEntity      = (string) array_key_first($searchableEntities);
        $entityType         = $_GET['entity'] ?? $defaultEntity;

        // Si l'entité demandée n'est pas accessible pour ce rôle, on replie sur la première disponible
        if (!isset($searchableEntities[$entityType])) {
            $entityType = $defaultEntity;
        }

        $config = $searchableEntities[$entityType];
        $repository = new SearchableRepository($config['model']);

        $offset = ($page - 1) * $perPage;

        $results = $repository->search(
            searchTerm: $searchTerm,
            searchableColumns: $config['columns'],
            filters: $this->getFiltersFromRequest(),
            orderBy: $_GET['sort'] ?? $config['default_sort'],
            orderDirection: $_GET['order'] ?? 'ASC',
            limit: $perPage,
            offset: $offset
        );

        $total = $repository->countSearch(
            searchTerm: $searchTerm,
            searchableColumns: $config['columns'],
            filters: $this->getFiltersFromRequest()
        );

        $hydratedResults = $this->filterByAccessibleIds($results, $entityType);
        $total           = count($hydratedResults);

        if ($this->isApiRequest()) {
            return $this->json([
                'data' => $hydratedResults,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => max(1, (int) ceil($total / $perPage)),
                ],
                'search_term' => $searchTerm,
            ]);
        }

        return $this->render('search/results.php', ['pageTitle' => 'Recherche',
            'results'            => $hydratedResults,
            'searchTerm'         => $searchTerm,
            'entityType'         => $entityType,
            'total'              => $total,
            'currentPage'        => $page,
            'lastPage'           => max(1, (int) ceil($total / $perPage)),
            'searchableEntities' => $searchableEntities,
        ]);
    }

    /**
     * Recherche globale (tous les types d'entités)
     * GET /search/global?q=terme
     */
    #[OA\Get(
        path: '/search/global',
        summary: 'Global search across all entities (JSON)',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Global search results'),
            new OA\Response(response: 400, description: 'Search term required'),
        ]
    )]
    public function globalSearch()
    {
        $searchTerm = $_GET['q'] ?? '';

        if (empty($searchTerm)) {
            return $this->json(['error' => 'Terme de recherche requis'], 400);
        }

        $results = [];
        $searchableEntities = $this->getSearchableEntities();

        foreach ($searchableEntities as $entityType => $config) {
            $repository = new SearchableRepository($config['model']);

            $entityResults = $repository->search(
                searchTerm: $searchTerm,
                searchableColumns: $config['columns'],
                limit: 5
            );

            $filtered = $this->filterByAccessibleIds($entityResults, $entityType);
            $results[$entityType] = [
                'label' => $config['label'],
                'count' => count($filtered),
                'items' => $filtered,
            ];
        }

        return $this->json([
            'search_term' => $searchTerm,
            'results' => $results,
        ]);
    }

    /**
     * Autocomplétion pour la barre de recherche
     * GET /search/autocomplete?q=terme&entity=users
     */
    #[OA\Get(
        path: '/search/autocomplete',
        summary: 'Search autocomplete (JSON)',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'entity', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['users', 'clients', 'projects', 'tasks'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Autocomplete suggestions'),
        ]
    )]
    public function autocomplete()
    {
        $searchTerm         = $_GET['q'] ?? '';
        $searchableEntities = $this->getSearchableEntities();
        $defaultEntity      = (string) array_key_first($searchableEntities);
        $entityType         = $_GET['entity'] ?? $defaultEntity;

        if (strlen($searchTerm) < 2) {
            return $this->json([]);
        }

        if (!isset($searchableEntities[$entityType])) {
            $entityType = $defaultEntity;
        }

        $config = $searchableEntities[$entityType];
        $repository = new SearchableRepository($config['model']);

        $results = $repository->search(
            searchTerm: $searchTerm,
            searchableColumns: $config['columns'],
            limit: 10
        );

        $results = $this->filterByAccessibleIds($results, $entityType);

        $suggestions = array_map(function ($result) use ($config) {
            $label = [];
            $data = method_exists($result, 'toArray') ? $result->toArray() : (array) $result;

            foreach ($config['display'] as $column) {
                if (isset($data[$column])) {
                    $label[] = $data[$column];
                }
            }

            return [
                'id' => method_exists($result, 'getId') ? $result->getId() : ($data['id'] ?? null),
                'label' => implode(' - ', $label),
                'data' => $data,
            ];
        }, $results);

        return $this->json($suggestions);
    }

    /**
     * Pour USER : retourne les IDs des entités accessibles (tasks ou projects).
     * Retourne null si l'utilisateur a accès à tout (rôle non USER).
     */
    private function getAccessibleIds(string $entityType): ?array
    {
        $currentUser = \Kentec\Kernel\Security\Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

        if ($userRole !== 'USER' || !$currentUser) {
            return null; // accès complet
        }

        $userId   = $currentUser->getId();
        $taskRepo = new \Kentec\Kernel\Database\Repository(\Kentec\App\Model\Task::class);

        if ($entityType === 'tasks') {
            $rows = $taskRepo->customQuery(
                'SELECT task_id FROM usertaskREL WHERE user_id = :userId',
                ['userId' => $userId]
            ) ?? [];
            return array_column($rows, 'task_id');
        }

        if ($entityType === 'projects') {
            $rows = $taskRepo->customQuery(
                'SELECT DISTINCT t.project_id FROM task t
                 INNER JOIN usertaskREL ur ON ur.task_id = t.id
                 WHERE ur.user_id = :userId AND t.project_id IS NOT NULL',
                ['userId' => $userId]
            ) ?? [];
            return array_column($rows, 'project_id');
        }

        return null;
    }

    /**
     * Filtre une liste de résultats aux IDs accessibles (si USER).
     */
    private function filterByAccessibleIds(array $results, string $entityType): array
    {
        $allowed = $this->getAccessibleIds($entityType);
        if ($allowed === null) {
            return $results; // pas de restriction
        }

        return array_values(array_filter($results, function ($item) use ($allowed) {
            $id = method_exists($item, 'getId') ? $item->getId() : ($item['id'] ?? null);
            return in_array($id, $allowed, true);
        }));
    }

    /**
     * Extrait les filtres de la requête
     */
    private function getFiltersFromRequest(): array
    {
        $filters = [];
        if (isset($_GET['filter']) && is_array($_GET['filter'])) {
            foreach ($_GET['filter'] as $key => $value) {
                if (!empty($value)) {
                    $filters[$key] = $value;
                }
            }
        }

        return $filters;
    }

    /**
     * Vérifie si la requête est une requête API
     */
    private function isApiRequest(): bool
    {
        return (isset($_GET['format']) && 'json' === $_GET['format'])
            || (isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
