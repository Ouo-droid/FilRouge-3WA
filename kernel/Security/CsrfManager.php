<?php

declare(strict_types=1);

namespace Kentec\Kernel\Security;

/**
 * Classe de protection contre les attaques CSRF (Cross-Site Request Forgery)
 * 
 * Cette classe protège l'application en implémentant un système de tokens CSRF.
 * Elle fonctionne en :
 * 1. Générant un token unique et aléatoire pour chaque session utilisateur
 * 2. Stockant ce token en session côté serveur
 * 3. Validant que chaque requête (POST, PUT, DELETE, etc.) contient le token correct
 * 4. Rejetant les requêtes avec un token invalide ou absent
 * 
 * Cela empêche un attaquant d'un site tiers d'effectuer des actions au nom 
 * de l'utilisateur, car il ne peut pas accéder au token stocké en session.
 * L'attaquant ne peut donc pas forger une requête valide.
 * 
 * @package Security
 * @category CSRF Protection
 */
class CsrfManager
{

    // Le préfixe "kentec_" évite les collisions avec d'autres applications
    private const TOKEN_SESSION_KEY = 'kentec_csrf_token';


    // Nombre d'octets pour générer le token.
    private const TOKEN_BYTE_LENGTH = 32;

    /**
     * Génère et retourne un token CSRF pour la session active.
     *
     * @return string Token CSRF hexadécimal (64 caractères)
     */
    public static function generateToken(): string
    {
        // Si le token n'existe pas encore en session, on en génère un nouveau
        if (!isset($_SESSION[self::TOKEN_SESSION_KEY])) {
            // bin2hex convertit des octets binaires aléatoires en chaîne hexadécimale lisible
            // random_bytes(32) = 32 octets = 256 bits de sécurité
            $_SESSION[self::TOKEN_SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_BYTE_LENGTH));
        }

        return $_SESSION[self::TOKEN_SESSION_KEY];
    }

    /**
     * Vérifie qu'un token CSRF soumis est valide.
     *
     * @param string $tokenSoumis Token reçu depuis $_POST['_csrf_token']
     * @return bool true si le token est valide, false sinon
     */
    public static function validateToken(string $tokenSoumis): bool
    {
        // Récupère le token attendu depuis la session
        $tokenEnSession = $_SESSION[self::TOKEN_SESSION_KEY] ?? '';

        // Les deux tokens doivent être non vides ET identiques
        if (empty($tokenEnSession) || empty($tokenSoumis)) {
            return false;
        }

        // Comparaison en temps constant pour éviter les timing attacks
        return hash_equals($tokenEnSession, $tokenSoumis);
    }

     // Invalide le token CSRF courant (le supprime de la session).
    public static function invalidateToken(): void
    {
        unset($_SESSION[self::TOKEN_SESSION_KEY]);
    }
}
