<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Information;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use OpenApi\Attributes as OA;

class InformationController extends AbstractController
{
    // API - Récupérer toutes les informations en JSON
    #[OA\Get(
        path: '/api/informations',
        summary: 'Get all informations (JSON)',
        tags: ['Information'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'informations', type: 'array', items: new OA\Items(ref: '#/components/schemas/Information')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiInformations(): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $infoRepo = new Repository(Information::class);
                $informations = $infoRepo->getAll();

                if (!$informations) {
                    $this->json([
                        'informations' => [],
                    ]);

                    return;
                }

                $informationsArray = [];
                foreach ($informations as $information) {
                    $informationsArray[] = $information->toArray();
                }

                $this->json([
                    'informations' => $informationsArray,
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

    // API - Récupérer une information par ID
    #[OA\Get(
        path: '/api/information/{id}',
        summary: 'Get information by ID (JSON)',
        tags: ['Information'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'information', ref: '#/components/schemas/Information'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Information not found'),
        ]
    )]
    final public function getApiInformation(string $id): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $infoRepo = new Repository(Information::class);
                $information = $infoRepo->getById($id);

                if (!$information) {
                    throw new \Exception('No information found');
                }

                $this->json([
                    'information' => $information->toArray(),
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

    // API - Récupérer les informations d'un utilisateur
    #[OA\Get(
        path: '/api/user/{userId}/informations',
        summary: 'Get informations by User ID (JSON)',
        tags: ['Information'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'informations', type: 'array', items: new OA\Items(ref: '#/components/schemas/Information')),
                    ]
                )
            ),
        ]
    )]
    final public function getApiUserInformations(string $userId): void
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            try {
                $infoRepo = new Repository(Information::class);
                $informations = $infoRepo->getByAttributes(['user_id' => $userId]);

                if (!$informations) {
                    $this->json([
                        'informations' => [],
                    ]);

                    return;
                }

                $informationsArray = [];
                foreach ($informations as $information) {
                    $informationsArray[] = $information->toArray();
                }

                $this->json([
                    'informations' => $informationsArray,
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

    // API - Ajouter une information
    #[OA\Post(
        path: '/api/add/information',
        summary: 'Add new information (JSON)',
        tags: ['Information'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'text'],
                properties: [
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'text', type: 'string'),
                    new OA\Property(property: 'isread', type: 'boolean'),
                    new OA\Property(property: 'userId', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Information created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function addApiInformation(): void
    {
        $this->verifyCsrf();
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            try {
                $inputData = json_decode(file_get_contents('php://input'), true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                if (empty($inputData['type'])) {
                    throw new \Exception('Information type is required');
                }

                $infoRepo = new Repository(Information::class);
                $information = new Information();

                $information->setType($inputData['type']);
                $information->setText($inputData['text'] ?? null);
                $information->setIsread($inputData['isread'] ?? false);
                $information->setUserId($inputData['userId'] ?? null);

                $infoRepo->insert($information);

                $this->json([
                    'success' => true,
                    'message' => 'Information created successfully',
                    'information' => $information->toArray(),
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

    // API - Modifier une information
    #[OA\Put(
        path: '/api/edit/information/{id}',
        summary: 'Edit information (JSON)',
        tags: ['Information'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'text', type: 'string'),
                    new OA\Property(property: 'isread', type: 'boolean'),
                    new OA\Property(property: 'userId', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Information updated'),
            new OA\Response(response: 404, description: 'Information not found'),
        ]
    )]
    final public function editApiInformation(string $id): void
    {
        $this->verifyCsrf();
        if ('PUT' === $_SERVER['REQUEST_METHOD']) {
            try {
                $inputData = json_decode(file_get_contents('php://input'), true);

                if (!$inputData) {
                    throw new \Exception('No data received');
                }

                $infoRepo = new Repository(Information::class);
                $information = $infoRepo->getById($id);

                if (!$information) {
                    throw new \Exception('Information not found');
                }

                if (isset($inputData['type'])) {
                    $information->setType($inputData['type']);
                }
                if (isset($inputData['text'])) {
                    $information->setText($inputData['text']);
                }
                if (isset($inputData['isread'])) {
                    $information->setIsread($inputData['isread']);
                }
                if (isset($inputData['userId'])) {
                    $information->setUserId($inputData['userId']);
                }

                $infoRepo->update($information);

                $this->json([
                    'success' => true,
                    'message' => 'Information updated successfully',
                    'information' => $information->toArray(),
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

    // API - Supprimer une information
    #[OA\Delete(
        path: '/api/delete/information/{id}',
        summary: 'Delete information',
        tags: ['Information'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Information deleted'),
            new OA\Response(response: 404, description: 'Information not found'),
        ]
    )]
    final public function deleteApiInformation(string $id): void
    {
        $this->verifyCsrf();
        if ('DELETE' === $_SERVER['REQUEST_METHOD']) {
            try {
                $infoRepo = new Repository(Information::class);
                $information = $infoRepo->getById($id);

                if (!$information) {
                    $this->json([
                        'success' => false,
                        'error' => 'Information not found',
                    ]);

                    return;
                }

                $deleted = $infoRepo->delete($id);

                if ($deleted) {
                    $this->json([
                        'success' => true,
                        'delete' => true,
                        'message' => 'Information deleted successfully',
                    ]);
                } else {
                    $this->json([
                        'success' => false,
                        'error' => 'Failed to delete information',
                    ]);
                }
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
}
