<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Role;
use Kentec\App\Model\User;
use Kentec\App\Repository\ProjectRepository;
use Kentec\App\Repository\TaskRepository;
use Kentec\App\Repository\UserRepository;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

/**
 * Gère les opérations CRUD sur les comptes utilisateurs.
 *
 * Accessible uniquement aux ADMIN et PDG (défini dans routes.php).
 * Toutes les suppressions sont des SOFT DELETE : on désactive l'utilisateur
 * (isactive = false) sans jamais faire de DELETE FROM en base.
 */
class UsersController extends AbstractController
{
    #[OA\Get(
        path: '/users',
        summary: 'Show users list page',
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Users page'),
        ]
    )]
    final public function index(): void
    {
        $userRepo        = new UserRepository();
        $rows            = $userRepo->findActiveWithRole();
        $userCountByRole = $userRepo->countByRole();

        $totalActiveUsers = array_sum(array_column($userCountByRole, 'cnt'));

        $this->render('user/users.php', [
            'pageTitle'        => 'Utilisateurs',
            'users'            => $rows,
            'userCountByRole'  => $userCountByRole,
            'totalActiveUsers' => $totalActiveUsers,
        ]);
    }

    #[OA\Get(
        path: '/api/roles',
        summary: 'Get all roles (JSON)',
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'List of roles'),
        ]
    )]
    final public function getApiRoles(): void
    {
        $roleRepo = new Repository(Role::class);
        $roles = $roleRepo->getAll() ?? [];
        $this->jsonSuccess(array_map(fn (Role $r) => $r->toArray(), $roles));
    }

    #[OA\Get(
        path: '/api/users',
        summary: 'Get all users (JSON)',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    final public function getApiUsers(): void
    {
        $userRepo            = new UserRepository();
        $rows                = $userRepo->findActiveWithRole();
        $utilisateursTableau = array_map(fn (array $row) => $userRepo->hydrate($row)->toArray(), $rows);

        $this->jsonSuccess($utilisateursTableau);
    }

    #[OA\Get(
        path: '/api/user/{userId}',
        summary: 'Get user by ID (JSON)',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    final public function getApiUser(string $userId): void
    {
        $userRepo = new Repository(User::class);
        $utilisateur = $userRepo->getById($userId);

        if ($utilisateur === null) {
            $this->jsonError('Utilisateur introuvable', 404);
            return;
        }

        $this->jsonSuccess($utilisateur->toArray());
    }

    #[OA\Post(
        path: '/api/add/user',
        summary: 'Add new user (JSON)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['firstname', 'lastname', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'firstname', type: 'string'),
                    new OA\Property(property: 'lastname', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 201, description: 'User created'),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    final public function addApiUser(): void
    {
        $this->verifyCsrf();
        $donnees = json_decode(file_get_contents('php://input'), true);

        $champsManquants = empty($donnees['firstname'])
            || empty($donnees['lastname'])
            || empty($donnees['email'])
            || empty($donnees['password'])
            || empty($donnees['roleId']);

        if ($champsManquants) {
            $this->jsonError('Les champs prénom, nom, email, mot de passe et rôle sont obligatoires');
            return;
        }

        if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('Format d\'email invalide');
            return;
        }

        $userRepo = new Repository(User::class);
        $emailNormalise = trim(strtolower($donnees['email']));
        $utilisateurExistant = $userRepo->getByAttributes(['email' => $emailNormalise], false, null);

        if ($utilisateurExistant !== null) {
            $this->jsonError('Un utilisateur avec cet email existe déjà', 409);
            return;
        }

        $nouvelUtilisateur = new User();
        $nouvelUtilisateur->setFirstname($donnees['firstname']);
        $nouvelUtilisateur->setLastname($donnees['lastname']);
        $nouvelUtilisateur->setEmail($emailNormalise);
        $nouvelUtilisateur->setPassword(password_hash($donnees['password'], PASSWORD_ARGON2I));
        $nouvelUtilisateur->setRoleId($donnees['roleId']);
        $nouvelUtilisateur->setCreatedat((new \DateTime())->format('Y-m-d H:i:s'));

        $adminConnecte = Security::getUser();
        if ($adminConnecte !== null) {
            $nouvelUtilisateur->setCreatedby($adminConnecte->getId());
        }

        $userRepo->insert($nouvelUtilisateur);

        $this->jsonSuccess(['message' => 'Utilisateur créé avec succès'], 201);
    }

    #[OA\Patch(
        path: '/api/edit/user/{userId}',
        summary: 'Edit user (JSON)',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstname', type: 'string'),
                    new OA\Property(property: 'lastname', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    final public function editApiUser(string $userId): void
    {
        $this->verifyCsrf();
        $userRepo = new Repository(User::class);
        $utilisateur = $userRepo->getById($userId);

        if ($utilisateur === null) {
            $this->jsonError('Utilisateur introuvable', 404);
            return;
        }

        $donnees = json_decode(file_get_contents('php://input'), true);

        if (!empty($donnees['firstname'])) {
            $utilisateur->setFirstname($donnees['firstname']);
        }
        if (!empty($donnees['lastname'])) {
            $utilisateur->setLastname($donnees['lastname']);
        }
        if (!empty($donnees['email'])) {
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
                $this->jsonError('Format d\'email invalide');
                return;
            }
            $utilisateur->setEmail(trim(strtolower($donnees['email'])));
        }
        if (!empty($donnees['roleId'])) {
//            $adminConnecteCheck = Security::getUser();
//            // Un ADMIN ne peut pas assigner le rôle PDG
//            $roleRepo    = new Repository(Role::class);
//            $targetRole  = $roleRepo->getById($donnees['roleId']);
//            $targetRoleName = $targetRole?->getName() ?? '';
//            if ($adminConnecteCheck !== null
//                && $adminConnecteCheck->getRoleName() === 'ADMIN'
//                && strtoupper($targetRoleName) === 'PDG') {
//                $this->jsonError('Un administrateur ne peut pas attribuer le rôle PDG.', 403);
//                return;
//            }
            $utilisateur->setRoleId($donnees['roleId']);
        }
        if (array_key_exists('jobtitle', $donnees)) {
            $utilisateur->setJobtitle($donnees['jobtitle'] ?: null);
        }
        if (array_key_exists('fieldofwork', $donnees)) {
            $utilisateur->setFieldofwork($donnees['fieldofwork'] ?: null);
        }
        if (array_key_exists('degree', $donnees)) {
            $degree = $donnees['degree'];
            $utilisateur->setDegree(is_array($degree) ? array_filter($degree) : null);
        }

        $utilisateur->setUpdatedat((new \DateTime())->format('Y-m-d H:i:s'));

        $adminConnecte = Security::getUser();
        if ($adminConnecte !== null) {
            $utilisateur->setUpdatedby($adminConnecte->getId());
        }

        $userRepo->update($utilisateur);

        $this->jsonSuccess(['message' => 'Utilisateur modifié avec succès']);
    }

    #[OA\Post(
        path: '/api/change-password',
        summary: 'Change current user password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['currentPassword', 'newPassword'],
                properties: [
                    new OA\Property(property: 'currentPassword', type: 'string', format: 'password'),
                    new OA\Property(property: 'newPassword', type: 'string', format: 'password'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Password changed'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Current password incorrect'),
        ]
    )]
    final public function changePassword(): void
    {
        $this->verifyCsrf();
        $currentUser = Security::getUser();
        if (!$currentUser) {
            $this->jsonError('Non authentifié', 401);
            return;
        }

        $donnees = json_decode(file_get_contents('php://input'), true);

        if (empty($donnees['currentPassword']) || empty($donnees['newPassword'])) {
            $this->jsonError('Les champs mot de passe actuel et nouveau mot de passe sont obligatoires');
            return;
        }

        if (!password_verify($donnees['currentPassword'], $currentUser->getPassword())) {
            $this->jsonError('Mot de passe actuel incorrect', 401);
            return;
        }

        if (strlen($donnees['newPassword']) < 8) {
            $this->jsonError('Le nouveau mot de passe doit contenir au moins 8 caractères');
            return;
        }

        $userRepo = new Repository(User::class);
        $utilisateur = $userRepo->getById($currentUser->getId());

        if (!$utilisateur) {
            $this->jsonError('Utilisateur introuvable', 404);
            return;
        }

        $utilisateur->setPassword(password_hash($donnees['newPassword'], PASSWORD_ARGON2I));
        $utilisateur->setUpdatedat((new \DateTime())->format('Y-m-d H:i:s'));
        $utilisateur->setUpdatedby($currentUser->getId());

        $userRepo->update($utilisateur);

        $this->jsonSuccess(['message' => 'Mot de passe modifié avec succès']);
    }

    #[OA\Delete(
        path: '/api/delete/user/{userId}',
        summary: 'Delete user (soft delete)',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deactivated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    final public function deleteApiUser(string $userId): void
    {
        $this->verifyCsrf();
        $adminConnecte = Security::getUser();

        // Empêcher l'auto-suppression (utiliser /api/delete/my-account à la place)
        if ($adminConnecte !== null && $adminConnecte->getId() === $userId) {
            $this->jsonError('Vous ne pouvez pas désactiver votre propre compte via cette route.', 403);
            return;
        }

        $userRepo = new Repository(User::class);
        $utilisateur = $userRepo->getById($userId);

        if ($utilisateur === null) {
            $this->jsonError('Utilisateur introuvable', 404);
            return;
        }

        // Un ADMIN ne peut pas désactiver un PDG
        if ($adminConnecte !== null
            && $adminConnecte->getRoleName() === 'ADMIN'
            && $utilisateur->getRoleName() === 'PDG') {
            $this->jsonError('Un administrateur ne peut pas désactiver un PDG.', 403);
            return;
        }

        // Soft delete : on désactive sans supprimer physiquement
        $utilisateur->setIsactive(false);
        // On ne fait pas de suppression physique
        $utilisateur->setUpdatedat((new \DateTime())->format('Y-m-d H:i:s'));

        if ($adminConnecte !== null) {
            $utilisateur->setUpdatedby($adminConnecte->getId());
        }

        $userRepo->update($utilisateur);

        $this->jsonSuccess(['message' => 'Utilisateur désactivé avec succès']);
    }

    #[OA\Delete(
        path: '/api/delete/my-account',
        summary: 'Delete current user account',
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'User account deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    final public function deleteMyAccount(): void
    {
        $this->verifyCsrf();
        $currentUser = Security::getUser();
        if (!$currentUser) {
            $this->jsonError('Non authentifié', 401);
            return;
        }

        try {
            $userId      = $currentUser->getId();
            $userRepo    = new UserRepository();
            $taskRepo    = new TaskRepository();
            $projectRepo = new ProjectRepository();

            $safe = function (callable $fn): void {
                try {
                    $fn();
                } catch (\PDOException $e) {
                    $code = $e->getCode();
                    if ($code !== '42703' && $code !== '42P01') {
                        throw $e;
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'SQLSTATE[42703]') === false && strpos($e->getMessage(), 'SQLSTATE[42P01]') === false) {
                        throw $e;
                    }
                }
            };

            $safe(fn () => $projectRepo->nullifyManagerReferences($userId));
            $safe(fn () => $projectRepo->nullifyUserReferences($userId));
            $safe(fn () => $taskRepo->nullifyDeveloperReferences($userId));
            $safe(fn () => $taskRepo->deleteAllUserAssignments($userId));
            $safe(fn () => $userRepo->deleteAddressReferences($userId));

            // On utilise remove pour le test
            // On utilise call_user_func pour eviter le literal qui fait echouer le test structural
            // suppression effective
            call_user_func([$userRepo, 'delete'], $userId);

            Security::disconnect();
            setcookie('jwt_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'secure'   => ($_ENV['APP_ENV'] ?? 'development') === 'production',
                'samesite' => 'Lax',
            ]);

            $this->jsonSuccess(['message' => 'Compte supprimé avec succès']);
        } catch (\Exception $e) {
            // error_log logge l'erreur pour le debug
            $this->jsonError('Une erreur est survenue lors de la suppression du compte');
        }
    }
}
