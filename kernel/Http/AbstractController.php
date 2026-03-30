<?php

declare(strict_types=1);

namespace Kentec\Kernel\Http;

use Kentec\Kernel\Security\CsrfManager;

/**
 * Class AbstractController
 * Cette classe définit des méthodes communes à tous les contrôleurs.
 * Elle fournit des fonctionnalités pour :
 * - Envoyer des réponses JSON
 * - Rendre des vues HTML
 * - Effectuer des redirections HTTP
 * - Protéger les formulaires via les tokens CSRF
 */
class AbstractController
{
    /**
     * Méthode pour envoyer une réponse JSON au client.
     *
     * @param array $data   les données à encoder en JSON
     * @param int   $status (optionnel) Le code de statut HTTP à envoyer (par défaut 200)
     */
    final public function json(array $data, int $status = 200): void
    {
        try {
            $sanitizedData = $this->prepareData($data, true);
            $this->setHeaders($status, true);
            $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
            $jsonResponse = json_encode($sanitizedData, $jsonOptions);
            if (false === $jsonResponse) {
                throw new \RuntimeException('Impossible d\'encoder les données JSON');
            }
            echo $jsonResponse;
            exit;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erreur lors de l\'envoi de la réponse JSON', 500, $e);
        }
    }

    /**
     * Nettoie et prépare les données pour l'encodage JSON ou le rendu HTML.
     *
     * Deux modes :
     * - Mode HTML (isJson = false) : échappe les caractères spéciaux avec htmlspecialchars()
     *   pour éviter les attaques XSS dans les vues PHP. Traitement RÉCURSIF sur les tableaux imbriqués.
     * - Mode JSON (isJson = true) : encode en UTF-8 propre, gère les types numériques et booléens.
     *
     * @param array $data   Données à préparer
     * @param bool  $isJson true pour le mode JSON, false pour le mode HTML (par défaut false)
     *
     * @return array Données nettoyées et préparées
     */
    private function prepareData(array $data, bool $isJson = false): array
    {
        if (!$isJson) {
            return array_map(function ($value) {
                if (is_string($value)) {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                if (is_array($value)) {
                    return $this->prepareData($value, false);
                }
                return $value;
            }, $data);
        } else {
            return array_map(function ($value) use ($isJson) {
                if (is_string($value)) {
                    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
                if (is_numeric($value)) {
                    return $value;
                }
                if (is_bool($value)) {
                    return $value;
                }
                if (is_array($value)) {
                    return $this->prepareData($value, $isJson);
                }
                return null;
            }, $data);
        }
    }

    /**
     * Définit les en-têtes HTTP pour la réponse.
     *
     * @param int  $status  Code de statut HTTP
     * @param bool $isJson  true pour JSON, false pour HTML
     */
    private function setHeaders(int $status, bool $isJson = false): void
    {
        http_response_code($status);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        } else {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
    }

    /**
     * Retourne le token CSRF de la session courante.
     *
     * Ce token est automatiquement injecté dans les données de vue par render().
     * Note : si la session n'est pas active (ex: tests CLI), retourne une chaîne vide.
     *
     * @return string Token CSRF hexadécimal, ou '' si pas de session active
     */
    final public function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        return CsrfManager::generateToken();
    }

    /**
     * Affiche une vue HTML en y injectant des données dynamiques.
     *
     * Sécurité :
     * - Le chemin de la vue est validé pour empêcher le path traversal.
     * - Un token CSRF est automatiquement injecté dans les données de vue.
     *
     * @param string $path   Chemin relatif de la vue (ex: "home/index.php")
     * @param array  $data   Données à injecter dans la vue
     * @param int    $status Code HTTP (défaut 200)
     */
    final public function render(string $path, array $data = [], int $status = 200): void
    {
        try {
            if (str_contains($path, '..')) {
                throw new \InvalidArgumentException("Chemin de vue invalide : caractères '..' interdits");
            }

            $viewsDirectory = __DIR__ . '/../../src/Views/';
            $viewFullPath = $viewsDirectory . $path;

            $resolvedPath = realpath($viewFullPath);
            $resolvedViewsDir = realpath($viewsDirectory);

            if ($resolvedPath === false || !str_starts_with($resolvedPath, $resolvedViewsDir)) {
                throw new \InvalidArgumentException('Vue introuvable ou chemin non autorisé');
            }

            $csrfToken = $this->getCsrfToken();
            if ($csrfToken !== '') {
                $data['_csrf_token'] = $csrfToken;
            }

            $viewData = $this->prepareData($data);
            extract($viewData);
            $view = $resolvedPath;
            $this->setHeaders($status);
            include __DIR__ . '/../../src/Views/base.php';
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erreur lors du rendu de la vue', 500, $e);
        }
    }

    /**
     * Redirige l'utilisateur vers une URL interne.
     *
     * Sécurité : seules les URLs internes (commençant par "/") sont autorisées.
     *
     * @param string $route L'URL interne vers laquelle rediriger (ex: "/login", "/projects")
     */
    final public function redirect(string $route): void
    {
        if (!str_starts_with($route, '/') || str_starts_with($route, '//')) {
            throw new \InvalidArgumentException('Redirection non autorisée : seules les URLs internes sont acceptées');
        }

        header('Location: ' . $route);
        exit;
    }

    /**
     * Envoie une réponse JSON de succès au format standardisé.
     *
     * Format : {"success": true, "data": {...}}
     *
     * @param mixed $data   Les données à retourner
     * @param int   $status Code HTTP (défaut 200)
     */
    final public function jsonSuccess(mixed $data, int $status = 200): void
    {
        $this->json(['success' => true, 'data' => $data], $status);
    }

    /**
     * Envoie une réponse JSON d'erreur au format standardisé.
     *
     * Format : {"success": false, "error": "message d'erreur"}
     *
     * @param string $errorMessage Message d'erreur explicite
     * @param int    $status       Code HTTP d'erreur (défaut 400)
     */
    final public function jsonError(string $errorMessage, int $status = 400): void
    {
        $this->json(['success' => false, 'error' => $errorMessage], $status);
    }

    /**
     * Vérifie le token CSRF pour les endpoints API en écriture (POST, PUT, PATCH, DELETE).
     *
     * Le token est lu depuis le header HTTP "X-CSRF-Token" envoyé par le frontend.
     * En cas d'échec, répond immédiatement avec un 403 et arrête l'exécution.
     */
    final protected function verifyCsrf(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CsrfManager::validateToken($token)) {
            $this->jsonError('Requête invalide (token CSRF manquant ou expiré).', 403);
        }
    }

    /**
     * Affiche une page d'erreur HTTP inline (sans fichier de vue).
     *
     * @param int    $httpCode    Code HTTP de l'erreur (403, 404, 500...)
     * @param string $httpMessage Message court affiché à l'utilisateur
     */
    final public function renderHttpError(int $httpCode, string $httpMessage): void
    {
        http_response_code($httpCode);
        header('Content-Type: text/html; charset=utf-8');

        $safeMessage = htmlspecialchars($httpMessage, ENT_QUOTES, 'UTF-8');

        echo "<!DOCTYPE html><html lang=\"fr\"><head><meta charset=\"UTF-8\">";
        echo "<title>{$httpCode} — {$safeMessage}</title></head>";
        echo "<body><h1>{$httpCode}</h1><p>{$safeMessage}</p></body></html>";
        exit();
    }
}
