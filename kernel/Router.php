<?php

declare(strict_types=1);

namespace Kentec\Kernel;

use Kentec\Kernel\Security\JwtManager;
use Kentec\Kernel\Security\Security;

/**
 * Reçoit chaque requête HTTP, trouve la route correspondante dans routes.php,
 * vérifie les droits d'accès (auth + rôle), puis appelle le bon contrôleur.
 */
class Router
{
    /**
     * Dispatche la requête HTTP vers le contrôleur approprié.
     *
     * Étapes :
     *  1. Charge les routes depuis routes.php
     *  2. Extrait le chemin de l'URL (sans query string)
     *  3. Compare chaque route avec le chemin via regex
     *  4. Vérifie la méthode HTTP (GET, POST, etc.)
     *  5. Vérifie l'auth : session active OU restauration depuis le cookie JWT
     *  6. Vérifie le rôle si requis (RBAC)
     *  7. Instancie le contrôleur et appelle la méthode
     *
     * @throws \Exception Si aucune route ne matche, méthode HTTP invalide, ou accès non autorisé.
     */
    final public static function dispatch(): void
    {
        include __DIR__ . '/../routes.php';

        $currentPath = $_SERVER['REQUEST_URI'];
        if (str_contains($currentPath, '?')) {
            $currentPath = explode('?', $currentPath)[0];
        }

        $isRouteFound = false;

        foreach (ROUTES as $routePath => $routeConfig) {
            // Transformer les paramètres dynamiques {id} en regex.
            // [\w-]+ matche les lettres, chiffres, underscores ET tirets (nécessaire pour les UUID).
            $regexPattern = preg_replace('#\{(\w+)\}#', '([\w-]+)', $routePath);
            $regexPattern = '#^' . $regexPattern . '$#';

            if (preg_match($regexPattern, $currentPath, $matches)) {

                $allowedHttpMethods = (array) $routeConfig['HTTP_METHODS'];
                if (!\in_array($_SERVER['REQUEST_METHOD'], $allowedHttpMethods, true)) {
                    throw new \Exception('Method not allowed');
                }

                preg_match_all('#\{(\w+)\}#', $routePath, $parameterNames);
                $parameterNames = $parameterNames[1];

                array_shift($matches);
                $routeParameters = array_combine($parameterNames, $matches);

                // --- Vérification de l'authentification et des rôles (RBAC) ---
                //
                // Le paramètre AUTH dans routes.php accepte deux formats :
                //   AUTH => true          → toute personne connectée peut accéder
                //   AUTH => ['ADMIN','CDP'] → seuls ces rôles ont accès
                //
                if (isset($routeConfig['AUTH'])) {
                    // Si la session PHP est vide, on tente de la restaurer depuis le cookie JWT
                    if (!Security::isConnected() && isset($_COOKIE['jwt_token'])) {
                        self::tryRestoreSessionFromJwtCookie();
                    }

                    if (!Security::isConnected()) {
                        throw new \Exception('Unauthorized');
                    }

                    $authConfig = $routeConfig['AUTH'];
                    if (\is_array($authConfig) && !Security::hasRole($authConfig)) {
                        throw new \Exception('Forbidden');
                    }
                }

                $controllerClass = $_ENV['CONTROLLER_NAMESPACE'] . $routeConfig['CONTROLLER'];
                $methodName = $routeConfig['METHOD'];

                if (!class_exists($controllerClass)) {
                    throw new \Exception('Controller class not found: ' . $controllerClass);
                }

                $controllerInstance = new $controllerClass();

                if (!method_exists($controllerInstance, $methodName)) {
                    throw new \Exception('Method not found in controller: ' . $methodName);
                }

                $controllerInstance->$methodName(...array_values($routeParameters));

                $isRouteFound = true;
                break;
            }
        }

        if (!$isRouteFound) {
            throw new \Exception('No route found');
        }
    }

    /**
     * Tente de restaurer la session PHP depuis le cookie JWT.
     *
     * Si le JWT est valide et non expiré : charge l'utilisateur depuis la base
     * et le remet dans $_SESSION['USER'].
     *
     * Si le JWT est invalide ou expiré : supprime le cookie pour forcer
     * une nouvelle connexion.
     */
    private static function tryRestoreSessionFromJwtCookie(): void
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? null;

        if ($jwtSecret === null) {
            return;
        }

        $jwtToken = $_COOKIE['jwt_token'];
        $jwtManager = new JwtManager($jwtSecret);

        if ($jwtManager->validateToken($jwtToken)) {
            $tokenPayload = $jwtManager->decodeToken($jwtToken);
            $userId = $tokenPayload['user_id'] ?? null;

            if ($userId !== null) {
                Security::restoreSessionFromUserId((string) $userId);
            }
        } else {
            $isCookieSecure = ($_ENV['APP_ENV'] ?? 'development') === 'production';

            setcookie('jwt_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'secure'   => $isCookieSecure,
                'samesite' => 'Lax',
            ]);
        }
    }
}
