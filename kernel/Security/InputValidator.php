<?php

declare(strict_types=1);

namespace Kentec\Kernel\Security;

/**
 * Valide et nettoie les données reçues de l'extérieur (formulaires, API, URL).
 *
 * === POURQUOI VALIDER LES ENTRÉES ? ===
 * Toute donnée venant de l'utilisateur (navigateur, API client) peut être malveillante.
 * Un attaquant peut envoyer : du code JavaScript (XSS), du SQL (injection), des UUIDs falsifiés, etc.
 * La validation côté serveur est OBLIGATOIRE — la validation JavaScript côté client
 * peut être contournée en quelques secondes avec les outils de développeur du navigateur.
 *
 * === UTILISATION ===
 * // Valider un email avant de chercher en base :
 * if (!InputValidator::validateEmail($email)) {
 *     return $this->jsonError('Format email invalide', 400);
 * }
 *
 * // Valider un UUID reçu dans l'URL :
 * if (!InputValidator::validateUuid($userId)) {
 *     return $this->jsonError('Identifiant invalide', 400);
 * }
 */
class InputValidator
{
    /**
     * Valide qu'une chaîne est un email au format correct (RFC 5321/5322).
     *
     * filter_var() avec FILTER_VALIDATE_EMAIL est la méthode PHP officielle.
     * Elle vérifie la structure : quelquechose@domaine.extension
     * Elle ne vérifie PAS que le domaine existe réellement (pas de résolution DNS).
     *
     * @param string $email Adresse email à valider
     * @return bool true si le format est valide, false sinon
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valide qu'une chaîne est un UUID
     *
     * @param string $uuid Chaîne à valider comme UUID
     * @return bool true si c'est un UUID valide, false sinon
     */
    public static function validateUuid(string $uuid): bool
    {
        // Expression régulière pour le format UUID standard (insensible à la casse)
        $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        return (bool) preg_match($uuidRegex, $uuid);
    }

    /**
     * Valide qu'un texte respecte des contraintes de longueur.
     *
     * Utilise mb_strlen() (multi-byte string length) pour compter correctement
     * les caractères UTF-8 (ex: "é" = 1 caractère, pas 2 octets).
     *
     * Le trim() enlève les espaces en début et fin — un texte avec que des espaces
     * ne doit pas passer la validation de longueur minimale.
     *
     * @param string $texte     Texte à valider
     * @param int    $longueurMin Nombre minimum de caractères (défaut : 1)
     * @param int    $longueurMax Nombre maximum de caractères (défaut : 255)
     * @return bool true si le texte respecte les contraintes, false sinon
     */
    public static function validateText(string $texte, int $longueurMin = 1, int $longueurMax = 255): bool
    {
        // mb_strlen compte les caractères réels (pas les octets) en UTF-8
        $nombreCaracteres = mb_strlen(trim($texte), 'UTF-8');
        return $nombreCaracteres >= $longueurMin && $nombreCaracteres <= $longueurMax;
    }

    /**
     * Valide qu'une valeur est un entier dans une plage donnée.
     *
     * Accepte les entiers PHP natifs ET les chaînes numériques (ex: "42").
     * C'est utile pour les données venant d'un formulaire HTML (toujours des chaînes).
     *
     * @param mixed $valeur  Valeur à valider (int ou string numérique)
     * @param int   $minimum Valeur minimale acceptée (défaut : PHP_INT_MIN = -9223372036854775808)
     * @param int   $maximum Valeur maximale acceptée (défaut : PHP_INT_MAX = 9223372036854775807)
     * @return bool true si la valeur est un entier dans la plage, false sinon
     */
    public static function validateInteger(mixed $valeur, int $minimum = PHP_INT_MIN, int $maximum = PHP_INT_MAX): bool
    {
        // is_numeric() accepte "42", 42, "42.0" mais pas "42abc"
        if (!is_numeric($valeur)) {
            return false;
        }

        // On caste en int pour la comparaison de plage
        $valeurEntiere = (int) $valeur;
        return $valeurEntiere >= $minimum && $valeurEntiere <= $maximum;
    }

    /**
     * Nettoie un texte pour l'affichage HTML en échappant les caractères dangereux.
     *
     * Convertit les caractères spéciaux en entités HTML :
     *   <script>   → &lt;script&gt;    (empêche l'exécution JavaScript)
     *   "          → &quot;            (protège les attributs HTML)
     *   '          → &#039;            (protège les attributs HTML simples)
     *   &          → &amp;             (évite les entités HTML malformées)
     *
     * À utiliser AVANT d'afficher une donnée utilisateur dans du HTML.
     * Note : ce n'est PAS une validation — c'est une transformation.
     *
     * @param string $texte Texte brut potentiellement dangereux
     * @return string Texte sécurisé pour l'affichage HTML
     */
    public static function sanitizeText(string $texte): string
    {
        return htmlspecialchars(trim($texte), ENT_QUOTES, 'UTF-8');
    }
}
