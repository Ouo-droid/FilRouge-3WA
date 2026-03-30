<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Kernel;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le Router.
 *
 * On ne peut pas tester dispatch() directement (il dépend de $_SERVER, de routes.php
 * et de la base de données), mais on peut tester la logique isolée :
 *  - Construction de la regex depuis un pattern de route
 *  - Extraction des noms de paramètres dynamiques
 *  - Logique RBAC (vérification des rôles)
 */
class RouterTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Tests regex et paramètres de route (hérités de Story 1.1)
    // -----------------------------------------------------------------------

    /**
     * La regex doit matcher les UUID avec tirets.
     * ([\w-]+ accepte les lettres, chiffres, underscores ET tirets)
     */
    public function testUuidRegexMatchesUuidsWithDashes(): void
    {
        $regexPattern = $this->buildRegexFromRoute('/api/project/{projectId}');

        $this->assertMatchesRegularExpression($regexPattern, '/api/project/550e8400-e29b-41d4-a716-446655440000');
        $this->assertMatchesRegularExpression($regexPattern, '/api/project/550e8400e29b41d4a716446655440000');
        $this->assertMatchesRegularExpression($regexPattern, '/api/project/42');
    }

    /**
     * La regex ne doit pas matcher des caractères non autorisés (espaces, slashes).
     */
    public function testUuidRegexRejectsInvalidCharacters(): void
    {
        $regexPattern = $this->buildRegexFromRoute('/api/project/{projectId}');

        $this->assertDoesNotMatchRegularExpression($regexPattern, '/api/project/abc def');
        $this->assertDoesNotMatchRegularExpression($regexPattern, '/api/project/abc/def');
    }

    /**
     * Les noms de paramètres sont correctement extraits depuis le pattern de route.
     */
    public function testRouteParameterNameExtraction(): void
    {
        preg_match_all('#\{(\w+)\}#', '/api/edit/project/{projectId}', $parameterNames);
        $this->assertEquals(['projectId'], $parameterNames[1]);
    }

    /**
     * L'extraction fonctionne aussi avec plusieurs paramètres dans une même route.
     */
    public function testMultipleParameterExtraction(): void
    {
        preg_match_all('#\{(\w+)\}#', '/api/user/{userId}/task/{taskId}', $parameterNames);
        $this->assertEquals(['userId', 'taskId'], $parameterNames[1]);
    }

    // -----------------------------------------------------------------------
    // Tests RBAC — logique de vérification des rôles (Story 1.3)
    // -----------------------------------------------------------------------

    /**
     * AUTH => true : tout rôle authentifié doit passer (aucune vérification de rôle).
     * is_array(true) == false → pas d'appel à hasRole(), accès accordé.
     */
    public function testAuthTrueAccepteNimporteLequelConnecte(): void
    {
        $authConfig = true;

        // Simule la logique du Router : if (is_array($authConfig) && !hasRole(...))
        $accessRefuse = \is_array($authConfig) && !$this->simulerHasRole(['ADMIN'], 'USER');

        $this->assertFalse($accessRefuse, 'AUTH=true ne doit pas refuser l\'accès même à un USER');
    }

    /**
     * AUTH => ['ADMIN', 'CDP'] : un USER doit être refusé.
     */
    public function testAuthArrayRefuseUnRoleNonAutorise(): void
    {
        $authConfig    = ['ADMIN', 'CDP'];
        $roleActuel    = 'USER';

        $accessRefuse = \is_array($authConfig) && !$this->simulerHasRole($authConfig, $roleActuel);

        $this->assertTrue($accessRefuse, 'Un USER ne doit pas accéder à une route réservée ADMIN/CDP');
    }

    /**
     * AUTH => ['ADMIN', 'CDP'] : un CDP doit être autorisé.
     */
    public function testAuthArrayAutoriseUnRolePresent(): void
    {
        $authConfig = ['ADMIN', 'CDP'];
        $roleActuel = 'CDP';

        $accessRefuse = \is_array($authConfig) && !$this->simulerHasRole($authConfig, $roleActuel);

        $this->assertFalse($accessRefuse, 'Un CDP doit accéder à une route qui l\'autorise');
    }

    /**
     * AUTH => ['ADMIN', 'PDG'] : un PDG doit être autorisé.
     */
    public function testAuthArrayAutoriseLeRolePDG(): void
    {
        $authConfig = ['ADMIN', 'PDG'];
        $roleActuel = 'PDG';

        $accessRefuse = \is_array($authConfig) && !$this->simulerHasRole($authConfig, $roleActuel);

        $this->assertFalse($accessRefuse, 'Un PDG doit accéder à une route qui l\'autorise');
    }

    /**
     * Vérifie que les routes de création de projet sont bien restreintes à CDP+
     * (et non ouvertes à USER comme avant Story 1.3).
     */
    public function testRouteCreationProjetExclutUSER(): void
    {
        // Charger les routes définies dans routes.php
        // include_once évite la redéfinition de const ROUTES si routes.php est déjà chargé
        include_once __DIR__ . '/../../../routes.php';

        $authProjetCreation = ROUTES['/api/add/project']['AUTH'] ?? null;

        $this->assertIsArray($authProjetCreation, 'La route /api/add/project doit avoir AUTH en tableau de rôles');
        $this->assertNotContains('USER', $authProjetCreation, 'Le rôle USER ne doit pas pouvoir créer un projet');
        $this->assertContains('CDP', $authProjetCreation, 'Le rôle CDP doit pouvoir créer un projet');
    }

    /**
     * Vérifie que les routes de création de client sont bien restreintes à CDP+.
     */
    public function testRouteCreationClientExclutUSER(): void
    {
        // include_once évite la redéfinition de const ROUTES si routes.php est déjà chargé
        include_once __DIR__ . '/../../../routes.php';

        $authClientCreation = ROUTES['/api/add/client']['AUTH'] ?? null;

        $this->assertIsArray($authClientCreation, 'La route /api/add/client doit avoir AUTH en tableau de rôles');
        $this->assertNotContains('USER', $authClientCreation, 'Le rôle USER ne doit pas pouvoir créer un client');
        $this->assertContains('CDP', $authClientCreation, 'Le rôle CDP doit pouvoir créer un client');
    }

    /**
     * Vérifie que les routes de lecture (GET) sont ouvertes à tout utilisateur connecté.
     */
    public function testRoutesLectureUtilisentAuthTrue(): void
    {
        // include_once évite la redéfinition de const ROUTES si routes.php est déjà chargé
        include_once __DIR__ . '/../../../routes.php';

        $routesLecture = ['/api/projects', '/api/tasks', '/api/clients', '/api/users'];

        foreach ($routesLecture as $route) {
            $auth = ROUTES[$route]['AUTH'] ?? null;
            $this->assertTrue($auth === true, "La route {$route} devrait avoir AUTH=true");
        }
    }

    // -----------------------------------------------------------------------
    // Tests RBAC — routes utilisateurs (Story 1.4)
    // -----------------------------------------------------------------------

    /**
     * La page /users (HTML) doit être réservée aux ADMIN et PDG.
     * Un collaborateur (USER) ou un CDP ne doit pas y accéder (AC5).
     */
    public function testPageUsersEstReserveeAuxAdmins(): void
    {
        include_once __DIR__ . '/../../../routes.php';

        $authPageUsers = ROUTES['/users']['AUTH'] ?? null;

        $this->assertIsArray($authPageUsers, 'La route /users doit avoir AUTH en tableau de rôles, pas true');
        $this->assertContains('ADMIN', $authPageUsers, 'ADMIN doit avoir accès à la gestion des comptes');
        $this->assertNotContains('USER', $authPageUsers, 'Un collaborateur (USER) ne doit pas gérer les comptes');
        $this->assertNotContains('CDP', $authPageUsers, 'Un CDP ne doit pas accéder à la gestion des comptes');
    }

    /**
     * Les routes d'écriture sur les utilisateurs (création, modification, désactivation)
     * doivent être réservées aux ADMIN uniquement (AC5).
     */
    public function testRoutesEcritureUtilisateursReserveesAuxAdmins(): void
    {
        include_once __DIR__ . '/../../../routes.php';

        $routesEcritureUtilisateurs = [
            '/api/add/user',
            '/api/edit/user/{userId}',
            '/api/delete/user/{userId}',
        ];

        foreach ($routesEcritureUtilisateurs as $route) {
            $auth = ROUTES[$route]['AUTH'] ?? null;
            $this->assertIsArray($auth, "La route {$route} doit avoir AUTH en tableau de rôles");
            $this->assertContains('ADMIN', $auth, "ADMIN doit avoir accès à {$route}");
            $this->assertNotContains('USER', $auth, "Un collaborateur (USER) ne doit pas accéder à {$route}");
        }
    }

    /**
     * Il ne doit pas y avoir d'inscription publique.
     * La route /register doit être restreinte aux admins (AC6).
     */
    public function testPasInscriptionPubliqueRouteRegisterEstRestreinte(): void
    {
        include_once __DIR__ . '/../../../routes.php';

        $authRegister = ROUTES['/register']['AUTH'] ?? null;

        // AUTH doit être un tableau (pas absent, pas true = ouvert à tous)
        $this->assertIsArray($authRegister, 'La route /register ne doit pas être publique (pas d\'AUTH absent ni AUTH=true)');
        $this->assertNotContains('USER', $authRegister, 'Un USER ne peut pas s\'inscrire lui-même');
        $this->assertContains('ADMIN', $authRegister, 'Seul un ADMIN peut créer un compte via /register');
    }

    // -----------------------------------------------------------------------
    // Méthodes utilitaires privées
    // -----------------------------------------------------------------------

    /**
     * Simule Security::hasRole() sans dépendance à la session ou à la base.
     * Utilisé pour tester la logique RBAC de manière isolée.
     *
     * @param string[] $rolesAutorises
     */
    private function simulerHasRole(array $rolesAutorises, string $roleUtilisateur): bool
    {
        return \in_array($roleUtilisateur, $rolesAutorises, true);
    }

    /**
     * Construit le pattern regex tel que le Router le génère depuis un chemin de route.
     */
    private function buildRegexFromRoute(string $routePath): string
    {
        $pattern = preg_replace('#\{(\w+)\}#', '([\\w-]+)', $routePath);
        return "#^{$pattern}$#";
    }
}
