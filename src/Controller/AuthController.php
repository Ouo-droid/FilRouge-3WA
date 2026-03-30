<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\App\Model\Role;
use Kentec\App\Model\User;
use Kentec\Kernel\Database\Repository;
use Kentec\Kernel\Http\AbstractController;
use Kentec\Kernel\Security\JwtManager;
use Kentec\Kernel\Security\Security;
use OpenApi\Attributes as OA;

/**
 * Gère l'authentification des utilisateurs : connexion, déconnexion, inscription admin.
 */
class AuthController extends AbstractController
{
    #[OA\Post(
        path: '/login',
        summary: 'Login user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'password', type: 'string', format: 'password'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to home on success'),
            new OA\Response(response: 200, description: 'Login page with error message'),
        ]
    )]
    #[OA\Get(
        path: '/login',
        summary: 'Show login page',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Login page'),
        ]
    )]
    public function login(): void
    {
        if (Security::isConnected()) {
            $this->redirect('/');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- Rate limiting : max 5 tentatives par tranche de 15 minutes ---
            $ip          = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rateLimitKey = 'login_attempts_' . md5($ip);
            $now          = time();
            $windowSeconds = 15 * 60;
            $maxAttempts   = 5;

            $attempts = $_SESSION[$rateLimitKey] ?? ['count' => 0, 'window_start' => $now];
            if ($now - $attempts['window_start'] > $windowSeconds) {
                $attempts = ['count' => 0, 'window_start' => $now];
            }
            if ($attempts['count'] >= $maxAttempts) {
                $remaining = $windowSeconds - ($now - $attempts['window_start']);
                $this->render('auth/login.php', [
                    'pageTitle' => 'Connexion',
                    'message'   => sprintf('Trop de tentatives. Réessayez dans %d minute(s).', (int) ceil($remaining / 60)),
                ]);
                return;
            }

            // --- Vérification CSRF (doit être la PREMIÈRE vérification) ---
            $tokenCsrfSoumis = $_POST['_csrf_token'] ?? '';
            if (!Security::verifyCsrfToken($tokenCsrfSoumis)) {
                $this->render('auth/login.php', ['pageTitle' => 'Connexion', 'message' => 'Requête invalide. Veuillez réessayer.']);
                return;
            }

            $submittedEmail    = trim($_POST['email'] ?? '');
            $submittedPassword = $_POST['password'] ?? '';

            if (empty($submittedEmail) || empty($submittedPassword)) {
                $this->render('auth/login.php', ['pageTitle' => 'Connexion', 'message' => 'Veuillez remplir tous les champs.']);
                return;
            }

            if (!filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
                $this->render('auth/login.php', ['pageTitle' => 'Connexion', 'message' => 'Format d\'email invalide.']);
                return;
            }

            try {
                Security::authenticate('email', $submittedEmail, $submittedPassword);
                // Connexion réussie : réinitialiser le compteur
                unset($_SESSION[$rateLimitKey]);

                $authenticatedUser = Security::getUser();

                if ($authenticatedUser && $authenticatedUser->getRoleId()) {
                    $roleRepository = new Repository(Role::class);
                    $role = $roleRepository->getByAttributes(['id' => $authenticatedUser->getRoleId()], false);
                    if ($role) {
                        $authenticatedUser->setRoleName($role->getName());
                    }
                }

                $jwtSecret = $_ENV['JWT_SECRET'] ?? throw new \Exception('JWT_SECRET non défini dans .env');
                $jwtManager = new JwtManager($jwtSecret);

                $tokenPayload = [
                    'user_id' => $authenticatedUser->getId(),
                    'email'   => $authenticatedUser->getEmail(),
                    'role'    => $authenticatedUser->getRoleName(),
                    'iat'     => time(),
                    'exp'     => time() + (60 * 60 * 4),
                ];

                $jwtToken = $jwtManager->createToken($tokenPayload);

                $isCookieSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';

                setcookie('jwt_token', $jwtToken, [
                    'expires'  => time() + (60 * 60 * 4),
                    'path'     => '/',
                    'httponly' => true,
                    'secure'   => $isCookieSecure,
                    'samesite' => 'Lax',
                ]);

                $this->redirect('/');

            } catch (\Exception $loginException) {
                // Incrémenter le compteur d'échecs
                $attempts['count']++;
                $_SESSION[$rateLimitKey] = $attempts;
                $this->render('auth/login.php', ['pageTitle' => 'Connexion', 'message' => 'Identifiants incorrects.']);
            }
        } else {
            $this->render('auth/login.php', ['pageTitle' => 'Connexion']);
        }
    }

    #[OA\Get(
        path: '/logout',
        summary: 'Logout user',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to login'),
        ]
    )]
    public function logout(): void
    {
        Security::disconnect();

        $isCookieSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';

        setcookie('jwt_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => $isCookieSecure,
            'samesite' => 'Lax',
        ]);

        $this->redirect('/login');
    }

    #[OA\Post(
        path: '/register',
        summary: 'Register new user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['email', 'password', 'confirm_password'],
                    properties: [
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname', type: 'string'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'password', type: 'string', format: 'password'),
                        new OA\Property(property: 'confirm_password', type: 'string', format: 'password'),
                        new OA\Property(property: 'role_id', type: 'string', format: 'uuid'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to login on success'),
            new OA\Response(response: 200, description: 'Register page with error message'),
        ]
    )]
    
    #[OA\Get(
        path: '/register',
        summary: 'Show register page',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Register page'),
        ]
    )]
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tokenCsrfSoumis = $_POST['_csrf_token'] ?? '';
            if (!Security::verifyCsrfToken($tokenCsrfSoumis)) {
                $this->render('auth/register.php', ['pageTitle' => 'Créer un compte', 'message' => 'Requête invalide. Veuillez réessayer.']);
                return;
            }

            $submittedEmail           = trim($_POST['email'] ?? '');
            $submittedPassword        = $_POST['password'] ?? '';
            $submittedConfirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($submittedEmail) || !filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
                $this->render('auth/register.php', ['pageTitle' => 'Créer un compte', 'message' => 'Format d\'email invalide.']);
                return;
            }

            if (mb_strlen($submittedPassword) < 8) {
                $this->render('auth/register.php', ['pageTitle' => 'Créer un compte', 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
                return;
            }

            if ($submittedPassword !== $submittedConfirmPassword) {
                $this->render('auth/register.php', ['pageTitle' => 'Créer un compte', 'message' => 'Mots de passe non identiques.']);
                return;
            }

            $newUser = new User();
            $userRepository = new Repository(User::class);

            $newUser->setFirstname(trim($_POST['firstname'] ?? ''));
            $newUser->setLastname(trim($_POST['lastname'] ?? ''));
            $newUser->setEmail($submittedEmail);
            $newUser->setRoleId($_POST['role_id'] ?? null);

            $hashedPassword = password_hash($submittedPassword, PASSWORD_ARGON2I);
            $newUser->setPassword($hashedPassword);

            $userRepository->insert($newUser);
            $this->redirect('/login');
        } else {
            $roleRepository = new Repository(Role::class);
            $roles = $roleRepository->getAll() ?? [];
            $this->render('auth/register.php', ['pageTitle' => 'Créer un compte', 'roles' => $roles]);
        }
    }
}
