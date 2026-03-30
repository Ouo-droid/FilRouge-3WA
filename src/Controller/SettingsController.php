<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Role;
use Kentec\App\Model\User;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

class SettingsController extends AbstractController
{
    /**
     * Affiche la page des paramètres
     */
    #[OA\Get(
        path: '/settings',
        summary: 'Show settings page',
        tags: ['Settings'],
        responses: [
            new OA\Response(response: 200, description: 'Settings page'),
        ]
    )]
    public function index(): void
    {
        // Récupérer l'utilisateur depuis la session pour avoir l'ID
        $sessionUser = Security::getUser();

        // Rafraîchir les données depuis la base de données
        $userRepo = new Repository(User::class);
        $user = $userRepo->getById($sessionUser->getId());

        // Charger le nom du rôle depuis la table role
        if ($user && $user->getRoleId()) {
            $roleRepo = new Repository(Role::class);
            $role = $roleRepo->getById($user->getRoleId());
            if ($role) {
                $user->setRoleName($role->getName());
            }
        }

        $this->render('settings/settings.php', ['pageTitle' => 'Paramètres',
            'user' => $user,
        ]);
    }
}
