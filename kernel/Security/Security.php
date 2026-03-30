<?php

declare(strict_types=1);

namespace Kentec\Kernel\Security;

use Kentec\Kernel\Database\EntityInterfaces\UserInterface;
use Kentec\Kernel\Database\Repository;

/**
 * Fournit les fonctions d'authentification et de vérification de session.
 *
 * Toutes les méthodes sont statiques : on les appelle sans instancier la classe.
 * Exemple : Security::isConnected(), Security::authenticate(...)
 */
class Security
{
    /**
     * Vérifie si un utilisateur est connecté.
     * La session doit contenir un objet USER qui implémente UserInterface.
     */
    final public static function isConnected(): bool
    {
        return isset($_SESSION['USER']) && $_SESSION['USER'] instanceof UserInterface;
    }

    /**
     * Déconnecte l'utilisateur : supprime la session côté serveur.
     * Le cookie JWT est supprimé séparément dans AuthController::logout().
     */
    final public static function disconnect(): void
    {
        unset($_SESSION['USER']);
        session_destroy();
    }

    /**
     * Authentifie un utilisateur avec son identifiant (colonne) et son mot de passe.
     *
     * Flux :
     *  1. Cherche l'utilisateur en base par la colonne $identifier (ex: 'email')
     *  2. Vérifie le mot de passe avec password_verify()
     *  3. Si le hash actuel est bcrypt, le rehash en Argon2 de façon transparente
     *  4. Régénère l'ID de session pour prévenir le session fixation attack
     *  5. Stocke l'objet utilisateur dans $_SESSION['USER']
     *
     * Lance une \Exception avec un message GÉNÉRIQUE en cas d'échec
     * (on ne révèle pas si c'est l'email ou le mot de passe qui est faux).
     *
     * @param string $identifier    Nom de la colonne de recherche (ex: 'email')
     * @param string $loginValue    Valeur recherchée (ex: 'jean@exemple.com')
     * @param string $plainPassword Mot de passe en clair saisi par l'utilisateur
     *
     * @throws \Exception Si les identifiants sont invalides
     */
    final public static function authenticate(string $identifier, string $loginValue, string $plainPassword): void
    {
        $userClass = 'Kentec\App\Model\User';

        if (!class_exists($userClass)) {
            throw new \Exception('Identifiants invalides');
        }

        $implementedInterfaces = class_implements($userClass);
        if (!in_array(UserInterface::class, $implementedInterfaces ?: [])) {
            throw new \Exception('Identifiants invalides');
        }

        $repository = new Repository($userClass);
        $results    = $repository->getByAttributes([$identifier => $loginValue]);

        // On utilise le même message d'erreur que l'email soit inconnu ou le mot de passe faux.
        // Cela évite de donner des informations à un attaquant (enumeration attack).
        if (empty($results)) {
            throw new \Exception('Identifiants invalides');
        }

        $user = $results[0];

        if (!password_verify($plainPassword, $user->getPassword())) {
            throw new \Exception('Identifiants invalides');
        }

        // Rehash transparent : si le hash stocké est bcrypt (ancien format),
        // on le remplace par Argon2 à la prochaine connexion réussie.
        if (password_needs_rehash($user->getPassword(), PASSWORD_ARGON2I)) {
            $newHash = password_hash($plainPassword, PASSWORD_ARGON2I);
            $user->setPassword($newHash);
            $repository->update($user);
        }

        // Session fixation attack : régénérer l'ID de session après login
        session_regenerate_id(true);

        $_SESSION['USER'] = $user;
    }

    /**
     * Restaure la session depuis un user_id (utilisé quand la session a expiré
     * mais que le cookie JWT est encore valide).
     *
     * @param string $userId UUID de l'utilisateur (extrait du payload JWT)
     *
     * @return bool true si la session a pu être restaurée, false sinon
     */
    public static function restoreSessionFromUserId(string $userId): bool
    {
        $userClass = 'Kentec\App\Model\User';
        if (!class_exists($userClass)) {
            return false;
        }

        $userRepository = new Repository($userClass);
        $results        = $userRepository->getByAttributes(['id' => $userId]);

        if (empty($results)) {
            return false;
        }

        $user = $results[0];

        $roleClass = 'Kentec\App\Model\Role';
        if (class_exists($roleClass) && $user->getRoleId()) {
            $roleRepository = new Repository($roleClass);
            $role = $roleRepository->getByAttributes(['id' => $user->getRoleId()], false);
            if ($role) {
                $user->setRoleName($role->getName());
            }
        }

        $_SESSION['USER'] = $user;

        return true;
    }

    /**
     * Vérifie si l'utilisateur connecté possède l'un des rôles autorisés.
     *
     * @param string[] $allowedRoles Liste des rôles autorisés (ex: ['ADMIN', 'PDG'])
     */
    public static function hasRole(array $allowedRoles): bool
    {
        if (!self::isConnected()) {
            return false;
        }

        $userRole = $_SESSION['USER']->getRoleName();

        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Retourne l'utilisateur connecté, ou null si personne n'est connecté.
     */
    public static function getUser(): ?UserInterface
    {
        return self::isConnected() ? $_SESSION['USER'] : null;
    }

    /**
     * Vérifie qu'un token CSRF soumis est valide.
     *
     * Délègue la vérification à CsrfManager qui utilise hash_equals()
     * pour une comparaison en temps constant (résistante aux timing attacks).
     *
     * @param string $tokenSoumis Token reçu depuis $_POST['_csrf_token']
     *
     * @return bool true si le token est valide, false sinon
     */
    public static function verifyCsrfToken(string $tokenSoumis): bool
    {
        return CsrfManager::validateToken($tokenSoumis);
    }
}
