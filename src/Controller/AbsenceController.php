<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Absence;
use Kentec\App\Repository\AbsenceRepository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\InputValidator;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class AbsenceController extends AbstractController
{
    // ── GET /api/absences ──────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/absences',
        summary: 'List all absences',
        tags: ['Absences'],
        responses: [
            new OA\Response(response: 200, description: 'List of absences'),
        ]
    )]
    final public function getAll(): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        try {
            $repo     = new AbsenceRepository();
            $absences = $repo->findAll();
            $this->json(['success' => true, 'absences' => $absences]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── GET /api/absences/user/{userId} ────────────────────────────────────

    #[OA\Get(
        path: '/api/absences/user/{userId}',
        summary: 'Get absences for a user',
        tags: ['Absences'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Absences for the user'),
        ]
    )]
    final public function getByUser(string $userId): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        if (!InputValidator::validateUuid($userId)) {
            $this->json(['success' => false, 'error' => 'UUID invalide.']);
            return;
        }

        try {
            $repo     = new AbsenceRepository();
            $absences = $repo->findByUserId($userId);
            $this->json(['success' => true, 'absences' => $absences]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── GET /api/absences/active ───────────────────────────────────────────
    // Returns users who have an active absence today (for warning checks)

    #[OA\Get(
        path: '/api/absences/active',
        summary: 'Get users with active absence today',
        tags: ['Absences'],
        responses: [
            new OA\Response(response: 200, description: 'Active absences'),
        ]
    )]
    final public function getActive(): void
    {
        if ('GET' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        try {
            $repo     = new AbsenceRepository();
            $absences = $repo->findActiveTodayWithUsers();
            $this->json(['success' => true, 'absences' => $absences]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── POST /api/add/absence ──────────────────────────────────────────────

    #[OA\Post(
        path: '/api/add/absence',
        summary: 'Declare a new absence',
        tags: ['Absences'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['userId', 'startDate', 'endDate'],
                properties: [
                    new OA\Property(property: 'userId',    type: 'string', format: 'uuid'),
                    new OA\Property(property: 'reason',    type: 'string'),
                    new OA\Property(property: 'startDate', type: 'string', format: 'date'),
                    new OA\Property(property: 'endDate',   type: 'string', format: 'date'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Absence created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function add(): void
    {
        $this->verifyCsrf();
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['userId']) || !InputValidator::validateUuid($input['userId'])) {
                throw new \Exception("L'identifiant utilisateur est invalide.");
            }
            if (empty($input['startDate'])) {
                throw new \Exception("La date de début est obligatoire.");
            }
            if (empty($input['endDate'])) {
                throw new \Exception("La date de fin est obligatoire.");
            }

            $start = new \DateTime($input['startDate']);
            $end   = new \DateTime($input['endDate']);

            if ($end < $start) {
                throw new \Exception("La date de fin doit être postérieure à la date de début.");
            }

            $absence = new Absence();
            $absence->setUserId($input['userId']);
            $absence->setReason($input['reason'] ?? null);
            $absence->setStartdate($start->format('Y-m-d'));
            $absence->setEnddate($end->format('Y-m-d'));

            $repo = new AbsenceRepository();
            $repo->insert($absence);

            $this->json(['success' => true, 'message' => 'Absence déclarée avec succès.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── POST /api/request/my-absence ──────────────────────────────────────
    // Accessible à tous les rôles : l'userId est forcé à l'utilisateur connecté.

    final public function requestOwn(): void
    {
        $this->verifyCsrf();
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        $currentUser = Security::getUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'error' => 'Utilisateur non authentifié.']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['startDate'])) {
                throw new \Exception("La date de début est obligatoire.");
            }
            if (empty($input['endDate'])) {
                throw new \Exception("La date de fin est obligatoire.");
            }

            $start = new \DateTime($input['startDate']);
            $end   = new \DateTime($input['endDate']);

            if ($end < $start) {
                throw new \Exception("La date de fin doit être postérieure à la date de début.");
            }

            $absence = new Absence();
            $absence->setUserId($currentUser->getId());
            $absence->setReason($input['reason'] ?? null);
            $absence->setStartdate($start->format('Y-m-d'));
            $absence->setEnddate($end->format('Y-m-d'));

            $repo = new AbsenceRepository();
            $repo->insert($absence);

            $this->json(['success' => true, 'message' => 'Demande d\'absence enregistrée.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── DELETE /api/delete/absence/{id} ────────────────────────────────────

    #[OA\Delete(
        path: '/api/delete/absence/{id}',
        summary: 'Delete an absence',
        tags: ['Absences'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Absence deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    final public function delete(string $id): void
    {
        $this->verifyCsrf();
        if ('DELETE' !== $_SERVER['REQUEST_METHOD']) {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée.']);
            return;
        }

        if (!InputValidator::validateUuid($id)) {
            $this->json(['success' => false, 'error' => 'UUID invalide.']);
            return;
        }

        try {
            $repo    = new AbsenceRepository();
            $deleted = $repo->delete($id);

            if ($deleted) {
                $this->json(['success' => true, 'message' => 'Absence supprimée.']);
            } else {
                $this->json(['success' => false, 'error' => 'Absence introuvable.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
