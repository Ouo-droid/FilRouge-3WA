<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

/**
 * Audit de non-régression : headers de sécurité HTTP.
 *
 * === POURQUOI LES HEADERS DE SÉCURITÉ ? ===
 * Les navigateurs modernes lisent les headers HTTP pour comprendre comment
 * se comporter vis-à-vis du contenu d'une page :
 *
 * - Content-Security-Policy (CSP) : liste blanche des sources de scripts/styles autorisées.
 *   Bloque l'injection de scripts malveillants (XSS).
 *
 * - Strict-Transport-Security (HSTS) : force HTTPS pendant une durée donnée.
 *   Empêche les attaques "downgrade" vers HTTP non chiffré.
 *
 * - X-Content-Type-Options : empêche le navigateur de "deviner" le type MIME d'un fichier.
 *   Évite qu'un fichier PNG contenant du JavaScript soit exécuté.
 *
 * - X-Frame-Options : empêche la page d'être affichée dans une iframe.
 *   Protège contre le clickjacking (un attaquant superpose une iframe invisible).
 *
 * - Referrer-Policy : contrôle quelles informations sont envoyées dans le header Referer.
 *   Évite de fuiter des URLs sensibles vers des sites tiers.
 *
 * Ces tests vérifient que le code source de index.php définit bien ces headers.
 */
class SecurityHeadersTest extends TestCase
{
    /** Code source du point d'entrée de l'application */
    private string $codeIndexPhp;

    protected function setUp(): void
    {
        $this->codeIndexPhp = file_get_contents(__DIR__ . '/../../../public/index.php');
    }

    /**
     * index.php doit envoyer un header Content-Security-Policy.
     *
     * Le CSP est la protection principale contre les injections XSS.
     * Sans lui, un script malveillant injecté dans la page s'exécuterait librement.
     */
    public function testIndexEnvoieHeaderCsp(): void
    {
        $this->assertStringContainsString(
            'Content-Security-Policy',
            $this->codeIndexPhp,
            'index.php doit définir le header Content-Security-Policy'
        );
    }

    /**
     * Le CSP doit utiliser un nonce pour les scripts.
     *
     * Un nonce (number used once) est un token aléatoire ajouté à chaque réponse.
     * Seuls les scripts portant ce nonce exact sont autorisés à s'exécuter.
     * Cela empêche l'exécution de scripts injectés (qui n'auraient pas le bon nonce).
     */
    public function testCspUtiliseNoncePourScripts(): void
    {
        $this->assertStringContainsString(
            'nonce-',
            $this->codeIndexPhp,
            'Le CSP doit utiliser un nonce pour autoriser les scripts légitimes'
        );

        // Le nonce doit être généré avec random_bytes() pour être cryptographiquement sûr
        $this->assertStringContainsString(
            'random_bytes',
            $this->codeIndexPhp,
            'Le nonce doit être généré avec random_bytes() (source cryptographiquement sûre)'
        );
    }

    /**
     * index.php doit envoyer le header Strict-Transport-Security (HSTS).
     *
     * HSTS dit au navigateur : "pendant X secondes, accède TOUJOURS en HTTPS".
     * Même si l'utilisateur tape "http://...", le navigateur forcera "https://...".
     */
    public function testIndexEnvoieHeaderHsts(): void
    {
        $this->assertStringContainsString(
            'Strict-Transport-Security',
            $this->codeIndexPhp,
            'index.php doit définir le header HSTS pour forcer HTTPS'
        );
    }

    /**
     * index.php doit envoyer le header X-Content-Type-Options: nosniff.
     *
     * Sans ce header, un navigateur peut "deviner" qu'un fichier .jpg contient du JavaScript
     * et l'exécuter. Ce header interdit ce comportement.
     */
    public function testIndexEnvoieHeaderXContentTypeOptions(): void
    {
        $this->assertStringContainsString(
            'X-Content-Type-Options',
            $this->codeIndexPhp,
            'index.php doit définir le header X-Content-Type-Options'
        );

        $this->assertStringContainsString(
            'nosniff',
            $this->codeIndexPhp,
            'La valeur du header X-Content-Type-Options doit être "nosniff"'
        );
    }

    /**
     * index.php doit envoyer le header X-Frame-Options: DENY.
     *
     * Ce header empêche la page d'être chargée dans une iframe par un site tiers.
     * Protection contre le clickjacking : l'attaquant ne peut pas superposer une iframe invisible.
     */
    public function testIndexEnvoieHeaderXFrameOptions(): void
    {
        $this->assertStringContainsString(
            'X-Frame-Options',
            $this->codeIndexPhp,
            'index.php doit définir le header X-Frame-Options'
        );

        $this->assertStringContainsString(
            'DENY',
            $this->codeIndexPhp,
            'X-Frame-Options doit être défini à DENY pour interdire tout affichage en iframe'
        );
    }

    /**
     * Le CSP doit contenir frame-ancestors 'none'.
     *
     * frame-ancestors 'none' est l'équivalent moderne de X-Frame-Options: DENY dans le CSP.
     * Les deux sont définis pour maximiser la compatibilité avec tous les navigateurs.
     */
    public function testCspContientFrameAncestors(): void
    {
        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $this->codeIndexPhp,
            "Le CSP doit contenir frame-ancestors 'none' pour le clickjacking"
        );
    }

    /**
     * index.php doit envoyer le header Referrer-Policy.
     *
     * same-origin : le header Referer est envoyé uniquement pour les requêtes vers le même domaine.
     * Cela évite de fuiter des URLs sensibles (avec tokens, IDs) vers des sites tiers.
     */
    public function testIndexEnvoieHeaderReferrerPolicy(): void
    {
        $this->assertStringContainsString(
            'Referrer-Policy',
            $this->codeIndexPhp,
            'index.php doit définir le header Referrer-Policy'
        );
    }

    /**
     * Le mode debug doit pouvoir être désactivé via la variable DEBUG dans .env.
     *
     * En production, DEBUG=false doit empêcher l'affichage des stack traces PHP.
     * Un stack trace exposé révèle la structure interne du code à un attaquant
     * (noms de fichiers, numéros de ligne, variables, etc.).
     */
    public function testModeDebugDesactivableEnProduction(): void
    {
        // Vérifie que le code gère bien la variable DEBUG
        $this->assertStringContainsString(
            "DEBUG",
            $this->codeIndexPhp,
            'index.php doit lire la variable DEBUG depuis l\'environnement'
        );

        // Vérifie que quand DEBUG=false, les stack traces ne sont pas affichés
        $this->assertStringContainsString(
            "'false'",
            $this->codeIndexPhp,
            'index.php doit avoir une branche pour DEBUG === false (pas de stack trace en production)'
        );
    }

    /**
     * Le formulaire de login utilise Security::verifyCsrfToken() pour valider le token CSRF.
     *
     * Le code source d'AuthController doit contenir l'appel à verifyCsrfToken().
     */
    public function testAuthControllerValideTokenCsrfSurPost(): void
    {
        $codeAuthController = file_get_contents(__DIR__ . '/../../../src/Controller/AuthController.php');

        $this->assertStringContainsString(
            'verifyCsrfToken',
            $codeAuthController,
            'AuthController doit appeler Security::verifyCsrfToken() sur les requêtes POST'
        );

        $this->assertStringContainsString(
            '_csrf_token',
            $codeAuthController,
            'AuthController doit lire le token CSRF depuis $_POST[\'_csrf_token\']'
        );
    }
}
