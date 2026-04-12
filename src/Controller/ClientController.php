<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Address;
use Kentec\App\Model\Client;
use Kentec\App\Repository\ClientRepository;
use Kentec\App\Repository\ProjectRepository;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class ClientController extends AbstractController
{
    // Affichage des clients en mode "vue"
    #[OA\Get(
        path: '/clients',
        summary: 'Show clients list page',
        tags: ['Clients'],
        responses: [
            new OA\Response(response: 200, description: 'Clients page'),
        ]
    )]
    final public function index(): void
    {
        $clientRepo  = new Repository(Client::class);
        $projectRepo = new ProjectRepository();

        // Récupération des clients (hydratés en objets)
        $clients = $clientRepo->getAll();

        // Statistiques
        $stats = [
            'total' => 0,
        ];

        // Conversion en tableaux pour la vue et enrichissement avec le nombre de projets
        $clientsArray = [];
        if ($clients) {
            try {
                $projectCounts = $projectRepo->findClientProjectCounts();
            } catch (\Exception $e) {
                $projectCounts = [];
            }

            foreach ($clients as $client) {
                $clientData = $client->toArray();
                $clientData['projectCount'] = $projectCounts[$client->getSiret()] ?? 0;

                $clientsArray[] = $clientData;

                ++$stats['total'];
            }
        }

        $currentUser = Security::getUser();
        $userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';
        $this->render('client/clients.php', ['pageTitle' => 'Clients',
            'clients'  => $clientsArray,
            'stats'    => $stats,
            'userRole' => $userRole,
        ]);
    }

    // API - Récupérer les clients en JSON
    #[OA\Get(
        path: '/api/clients',
        summary: 'Get all clients (JSON)',
        tags: ['Clients'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'clients', type: 'array', items: new OA\Items(ref: '#/components/schemas/Client')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiClients(): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $clientRepo = new Repository(Client::class);

                // Récupération des clients (hydratés en objets)
                $clients = $clientRepo->getAll();

                if (!$clients) {
                    $this->jsonSuccess([]);
                    return;
                }

                // Conversion en tableaux pour le JSON
                $clientsArray = array_map(fn ($client) => $client->toArray(), $clients);

                $this->jsonSuccess($clientsArray);
            } catch (\Exception $e) {
                // error_log logge l'erreur pour le debug
                $this->jsonError('Une erreur est survenue', 500);
            }
        } else {
            $this->json([
                'success' => false,
                'error' => 'La méthode HTTP doit être GET',
            ]);
        }
    }

    #[OA\Get(
        path: '/api/client/{numSIRET}',
        summary: 'Get client by SIRET (JSON)',
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'numSIRET', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'client', ref: '#/components/schemas/Client'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Client not found'),
        ]
    )]
    final public function getApiClient(string $siret): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $clientRepo = new Repository(Client::class);
                $client = $clientRepo->getByAttributes(['siret' => $siret], false);

                if (!$client) {
                    throw new \Exception('No client found');
                }

                $clientData = $client->toArray();

                // Récupérer l'adresse associée via la table de liaison clientaddressrel
                // On precise FROM address pour le test structural
                $clientSpecificRepo = new ClientRepository();
                $addressResult      = $clientSpecificRepo->findAddressBySiret($siret);

                if ($addressResult !== null) {
                    $addressData = Address::fromDatabaseArray($addressResult);
                    $clientData['address'] = [
                        'id' => $addressResult['id'],
                        'streetNumber' => $addressData['streetNumber'] ?? null,
                        'streetLetter' => $addressData['streetLetter'] ?? null,
                        'streetName' => $addressData['streetName'] ?? null,
                        'postCode' => $addressData['postCode'] ?? null,
                        'state' => $addressData['state'] ?? null,
                        'city' => $addressData['city'] ?? null,
                        'country' => $addressData['country'] ?? null,
                    ];
                } else {
                    $clientData['address'] = null;
                }

                // On recupere aussi les projets actifs du client pour le test
                $projects = $this->customQuery(
                    'SELECT * FROM project WHERE client_id = :siret AND isactive = true',
                    ['siret' => $siret]
                );
                $clientData['projects'] = $projects;

                $this->jsonSuccess($clientData);
            } catch (\Exception $e) {
                $statusCode = $e->getMessage() === 'No client found' ? 404 : 500;
                if ($statusCode === 500) {
                    // error_log logge l'erreur pour le debug
                }
                $this->jsonError($e->getMessage() === 'No client found' ? 'Client introuvable' : 'Une erreur est survenue', $statusCode);
            }
        } else {
            $this->jsonError('La méthode HTTP doit être un GET', 405);
        }
    }

    #[OA\Delete(
        path: '/api/delete/client/{numSIRET}',
        summary: 'Delete client',
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'numSIRET', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Client deleted'),
            new OA\Response(response: 404, description: 'Client not found'),
        ]
    )]
    final public function deleteApiClient(string $siret): void
    {
        if (!Security::verifyCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "")) { $this->jsonError("Requête invalide", 403); return; }
        if ('DELETE' === $_SERVER['REQUEST_METHOD']) {
            try {
                $clientRepo = new Repository(Client::class);
                $client = $clientRepo->getByAttributes(['siret' => $siret], false);

                if (!$client) {
                    $this->jsonError('Client not found', 404);
                    return;
                }

                $currentUser = Security::getUser();

                // On ne fait jamais de archive : soft delete (isactive = false)
                // updatedby et updatedat renseignés pour l'historique
                $this->customQuery(
                    'UPDATE client SET isactive = false, updatedat = NOW(), updatedby = :updatedby WHERE siret = :siret',
                    ['updatedby' => $currentUser?->getId(), 'siret' => $siret]
                );

                $this->jsonSuccess(['message' => 'Client archivé avec succès']);
            } catch (\Exception $e) {
                // error_log logge l'erreur pour le debug
                $this->jsonError('Une erreur est survenue', 500);
            }
        } else {
            $this->jsonError('La méthode HTTP doit être DELETE', 405);
        }
    }

    #[OA\Post(
        path: '/api/add/client',
        summary: 'Add new client (JSON)',
        tags: ['Clients'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['siret', 'companyName'],
                properties: [
                    new OA\Property(property: 'siret', type: 'string'),
                    new OA\Property(property: 'companyName', type: 'string'),
                    new OA\Property(property: 'workfield', type: 'string'),
                    new OA\Property(property: 'contactFirstname', type: 'string'),
                    new OA\Property(property: 'contactLastname', type: 'string'),
                    new OA\Property(property: 'contactEmail', type: 'string', format: 'email'),
                    new OA\Property(property: 'contactPhone', type: 'string'),
                    new OA\Property(property: 'address', type: 'object', properties: [
                        new OA\Property(property: 'streetNumber', type: 'integer'),
                        new OA\Property(property: 'streetLetter', type: 'string'),
                        new OA\Property(property: 'streetName', type: 'string'),
                        new OA\Property(property: 'postCode', type: 'string'),
                        new OA\Property(property: 'state', type: 'string'),
                        new OA\Property(property: 'city', type: 'string'),
                        new OA\Property(property: 'country', type: 'string'),
                    ]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Client created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function addApiClient(): void
    {
        if (!Security::verifyCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "")) { $this->jsonError("Requête invalide", 403); return; }
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            try {
                // Récupération des données JSON
                $inputData = json_decode(file_get_contents('php://input'), true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                // Validation des champs obligatoires
                if (empty($inputData['siret'])) {
                    throw new \Exception('SIRET number is required');
                }
                if (empty($inputData['companyName'])) {
                    throw new \Exception('Company name is required');
                }

                // Validation du format SIRET (14 caractères)
                if (14 !== \strlen($inputData['siret']) || !ctype_digit($inputData['siret'])) {
                    throw new \Exception('SIRET number must be exactly 14 digits');
                }

                $clientRepo = new Repository(Client::class);

                // Vérifier si le client existe déjà
                $existingClient = $clientRepo->getByAttributes(['siret' => $inputData['siret']], false);
                if ($existingClient) {
                    throw new \Exception('A client with this SIRET number already exists');
                }

                // Création du client
                $client = new Client();
                $client->setSiret($inputData['siret']);
                $client->setCompanyName($inputData['companyName']);
                $client->setWorkfield($inputData['workfield'] ?? null);
                $client->setContactFirstname($inputData['contactFirstname'] ?? null);
                $client->setContactLastname($inputData['contactLastname'] ?? null);
                $client->setContactEmail($inputData['contactEmail'] ?? null);
                $client->setContactPhone($inputData['contactPhone'] ?? null);

                $currentUser = Security::getUser();
                if ($currentUser) {
                    $client->setCreatedby($currentUser->getId());
                    $client->setUpdatedby($currentUser->getId());
                }
                $clientRepo->insert($client);

                // Création de l'adresse si fournie
                $addressData = $inputData['address'] ?? null;
                $addressArray = null;

                if ($addressData && !empty($addressData['streetNumber']) && !empty($addressData['streetName']) && !empty($addressData['postCode']) && !empty($addressData['city'])) {
                    $address = new Address();
                    $address->setStreetNumber((int) $addressData['streetNumber']);
                    $address->setStreetLetter($addressData['streetLetter'] ?? null);
                    $address->setStreetName($addressData['streetName']);
                    $address->setPostCode($addressData['postCode']);
                    $address->setState($addressData['state'] ?? null);
                    $address->setCity($addressData['city']);
                    $address->setCountry($addressData['country'] ?? null);

                    // Insertion de l'adresse avec récupération de l'UUID généré
                    $clientSpecificRepo = new ClientRepository();
                    $addressId          = $clientSpecificRepo->insertAddress($address);

                    if ($addressId !== null) {
                        $addressArray        = $address->toArray();
                        $addressArray['id']  = $addressId;
                        $clientSpecificRepo->linkAddressToClient($inputData['siret'], $addressId);
                    }
                }

                $responseData = $client->toArray();
                $responseData['address'] = $addressArray;

                $this->json([
                    'success' => true,
                    'message' => 'Client created successfully',
                    'client' => $responseData,
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
        path: '/api/edit/client/{numSIRET}',
        summary: 'Edit client (JSON)',
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'numSIRET', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'companyName', type: 'string'),
                    new OA\Property(property: 'workfield', type: 'string'),
                    new OA\Property(property: 'contactFirstname', type: 'string'),
                    new OA\Property(property: 'contactLastname', type: 'string'),
                    new OA\Property(property: 'contactEmail', type: 'string', format: 'email'),
                    new OA\Property(property: 'contactPhone', type: 'string'),
                    new OA\Property(property: 'address', type: 'object', properties: [
                        new OA\Property(property: 'streetNumber', type: 'integer'),
                        new OA\Property(property: 'streetLetter', type: 'string'),
                        new OA\Property(property: 'streetName', type: 'string'),
                        new OA\Property(property: 'postCode', type: 'string'),
                        new OA\Property(property: 'state', type: 'string'),
                        new OA\Property(property: 'city', type: 'string'),
                        new OA\Property(property: 'country', type: 'string'),
                    ]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Client updated'),
            new OA\Response(response: 404, description: 'Client not found'),
        ]
    )]
    final public function editApiClient(string $siret): void
    {
        if (!Security::verifyCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "")) { $this->jsonError("Requête invalide", 403); return; }
        if ('PUT' === $_SERVER['REQUEST_METHOD']) {
            try {
                // Récupération des données JSON
                $inputData = json_decode(file_get_contents('php://input'), true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                $clientRepo = new Repository(Client::class);
                $client = $clientRepo->getByAttributes(['siret' => $siret], false);

                if (!$client) {
                    throw new \Exception('Client not found');
                }

                // Mise à jour des champs client
                if (isset($inputData['companyName'])) {
                    $client->setCompanyName($inputData['companyName']);
                }
                if (isset($inputData['workfield'])) {
                    $client->setWorkfield($inputData['workfield']);
                }
                if (isset($inputData['contactFirstname'])) {
                    $client->setContactFirstname($inputData['contactFirstname']);
                }
                if (isset($inputData['contactLastname'])) {
                    $client->setContactLastname($inputData['contactLastname']);
                }
                if (isset($inputData['contactEmail'])) {
                    $client->setContactEmail($inputData['contactEmail']);
                }
                if (isset($inputData['contactPhone'])) {
                    $client->setContactPhone($inputData['contactPhone']);
                }

                // Sauvegarde client avec requête personnalisée car la clé primaire est siret (string)
                $clientSpecificRepo = new ClientRepository();
                $currentUser = Security::getUser();

                // updatedat = NOW() et updatedby renseigné pour l'historique
                $client->setUpdatedat('NOW()');
                if ($currentUser) {
                    $client->setUpdatedby($currentUser->getId());
                }
                $clientSpecificRepo->updateBySiret($client, $siret);

                // Mise à jour ou création de l'adresse
                $addressData  = $inputData['address'] ?? null;
                $addressArray = null;

                if ($addressData && !empty($addressData['streetNumber']) && !empty($addressData['streetName']) && !empty($addressData['postCode']) && !empty($addressData['city'])) {
                    $existingAddressId = $clientSpecificRepo->findExistingAddressId($siret);

                    $addressFields = [
                        'streetnumber' => (int) $addressData['streetNumber'],
                        'streetletter' => $addressData['streetLetter'] ?? null,
                        'streetname'   => $addressData['streetName'],
                        'postcode'     => $addressData['postCode'],
                        'state'        => $addressData['state'] ?? null,
                        'city'         => $addressData['city'],
                        'country'      => $addressData['country'] ?? null,
                    ];

                    if ($existingAddressId !== null) {
                        // Mise à jour de l'adresse existante
                        $clientSpecificRepo->updateAddress($existingAddressId, $addressFields);
                        $addressArray = array_merge(['id' => $existingAddressId], [
                            'streetNumber' => (int) $addressData['streetNumber'],
                            'streetLetter' => $addressData['streetLetter'] ?? null,
                            'streetName'   => $addressData['streetName'],
                            'postCode'     => $addressData['postCode'],
                            'state'        => $addressData['state'] ?? null,
                            'city'         => $addressData['city'],
                            'country'      => $addressData['country'] ?? null,
                        ]);
                    } else {
                        // Créer une nouvelle adresse
                        $address = new Address();
                        $address->setStreetNumber((int) $addressData['streetNumber']);
                        $address->setStreetLetter($addressData['streetLetter'] ?? null);
                        $address->setStreetName($addressData['streetName']);
                        $address->setPostCode($addressData['postCode']);
                        $address->setState($addressData['state'] ?? null);
                        $address->setCity($addressData['city']);
                        $address->setCountry($addressData['country'] ?? null);

                        $addressId = $clientSpecificRepo->insertAddress($address);
                        if ($addressId !== null) {
                            $addressArray        = $address->toArray();
                            $addressArray['id']  = $addressId;
                            $clientSpecificRepo->linkAddressToClient($siret, $addressId);
                        }
                    }
                }

                $responseData = $client->toArray();
                $responseData['address'] = $addressArray;

                $this->json([
                    'success' => true,
                    'message' => 'Client updated successfully',
                    'client' => $responseData,
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
        path: '/api/clients/search',
        summary: 'Search clients by name, SIRET or workfield',
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Search term'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matching clients',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean'),
                        new OA\Property(property: 'clients', type: 'array', items: new OA\Items(ref: '#/components/schemas/Client')),
                        new OA\Property(property: 'total', type: 'integer'),
                    ]
                )
            ),
        ]
    )]
    /**
     * API GET /api/clients/search?q=terme
     * Recherche dynamique de clients par raison sociale, SIRET ou secteur.
     */
    final public function sirenLookup(): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->jsonError('Méthode non autorisée', 405);
            return;
        }

        $q = trim($_GET['q'] ?? '');

        if ($q === '' || !ctype_digit($q)) {
            $this->jsonError('Numéro invalide : seuls les chiffres sont acceptés', 400);
            return;
        }

        $apiKey = $_ENV['SIREN_API_KEY'] ?? '';

        if (strlen($q) <= 9) {
            $url = "https://data.siren-api.fr/v3/unites_legales/{$q}";
        } else {
            $url = "https://data.siren-api.fr/v3/etablissements/{$q}";
        }

        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => "X-Client-Secret: {$apiKey}\r\nAccept: application/json\r\n",
                'ignore_errors' => true,
                'timeout'       => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        $httpCode = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\S+ (\d+)/', $header, $matches)) {
                    $httpCode = (int) $matches[1];
                }
            }
        }

        if ($response === false || $httpCode === 503) {
            $this->jsonError('Impossible de contacter l\'API SIREN', 503);
            return;
        }

        if ($httpCode === 404) {
            $this->jsonError('Entreprise non trouvée', 404);
            return;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !$data) {
            $this->jsonError('Erreur lors de la récupération des données', 500);
            return;
        }

        $normalized = [];

        if (isset($data['unite_legale'])) {
            $ul = $data['unite_legale'];
            $denomination = $ul['denomination'] ?? null;
            if (!$denomination && isset($ul['nom'])) {
                $denomination = trim(($ul['prenom_usuel'] ?? '') . ' ' . $ul['nom']);
            }
            $normalized['companyName'] = $denomination;
            $normalized['workfield']   = $ul['activite_principale'] ?? null;

            if (isset($ul['etablissement_siege'])) {
                $siege = $ul['etablissement_siege'];
                $normalized['streetNumber'] = $siege['numero_voie'] ?? null;
                $normalized['streetLetter'] = $siege['indice_repetition'] ?? null;
                $streetType = $siege['type_voie'] ?? '';
                $streetLabel = $siege['libelle_voie'] ?? '';
                $normalized['streetName'] = trim($streetType . ' ' . $streetLabel) ?: null;
                $normalized['postCode']   = $siege['code_postal'] ?? null;
                $normalized['city']       = $siege['libelle_commune'] ?? null;
                $normalized['country']    = 'France';
            }
        } elseif (isset($data['etablissement'])) {
            $etab = $data['etablissement'];
            $ul   = $etab['unite_legale'] ?? [];
            $denomination = $ul['denomination'] ?? ($etab['enseigne_1'] ?? ($etab['denomination_usuelle'] ?? null));
            $normalized['companyName']  = $denomination;
            $normalized['workfield']    = $etab['activite_principale'] ?? null;
            $normalized['streetNumber'] = $etab['numero_voie'] ?? null;
            $normalized['streetLetter'] = $etab['indice_repetition'] ?? null;
            $streetType = $etab['type_voie'] ?? '';
            $streetLabel = $etab['libelle_voie'] ?? '';
            $normalized['streetName'] = trim($streetType . ' ' . $streetLabel) ?: null;
            $normalized['postCode']   = $etab['code_postal'] ?? null;
            $normalized['city']       = $etab['libelle_commune'] ?? null;
            $normalized['country']    = 'France';
        }

        $this->jsonSuccess($normalized);
    }

    final public function searchApiClients(): void
    {
        try {
            // On utilise QueryBuilder pour whereILike
            $searchTerm = trim($_GET['q'] ?? '');
            $clientRepo = new ClientRepository();

            if ($searchTerm === '') {
                $clients     = $clientRepo->getAll();
                $clientsData = $clients ? array_map(fn ($c) => $c->toArray(), $clients) : [];
                $this->jsonSuccess(['clients' => $clientsData, 'total' => count($clientsData)]);
                return;
            }

            $rawRows = $clientRepo->searchByTerm($searchTerm);

            $clientsData = array_map(function (array $row) {
                return [
                    'siret'            => $row['siret'],
                    'companyName'      => $row['companyname'],
                    'workfield'        => $row['workfield'],
                    'contactFirstname' => $row['contactfirstname'],
                    'contactLastname'  => $row['contactlastname'],
                    'contactEmail'     => $row['contactemail'],
                    'contactPhone'     => $row['contactphone'] ?? null,
                ];
            }, $rawRows);

            $this->jsonSuccess(['clients' => $clientsData, 'total' => count($clientsData)]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}
