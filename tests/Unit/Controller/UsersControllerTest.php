<?php

namespace Kentec\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour UsersController.
 *
 * Contrainte architecturale :
 * Le contrôleur instancie directement le Repository (new Repository())
 * sans injection de dépendances. Les tests d'intégration avec la vraie
 * base de données sont donc nécessaires pour tester le comportement complet.
 *
 * Ces tests vérifient les CONTRAINTES DE SÉCURITÉ du code :
 * - L'algorithme de hashage utilisé (ARGON2I et non DEFAULT)
 * - L'absence de DELETE physique (soft delete uniquement)
 * - La présence des champs d'audit (createdby, updatedby)
 * - La validation du format email
 * - Le format standardisé des réponses JSON
 *
 * Ce type de test est appelé "test de structure" ou "test de contrainte" :
 * on lit le code source et on vérifie qu'il respecte les règles de l'équipe.
 */
class UsersControllerTest extends TestCase
{
    /** Chemin absolu vers le fichier du contrôleur */
    private string $cheminFichierControleur;

    /** Code source du contrôleur (lu une seule fois pour tous les tests) */
    private string $codeSourceControleur;

    protected function setUp(): void
    {
        $this->cheminFichierControleur = __DIR__ . '/../../../src/Controller/UsersController.php';
        $this->codeSourceControleur = file_get_contents($this->cheminFichierControleur);
    }

    /**
     * Vérifie que le contrôleur utilise PASSWORD_ARGON2I et non PASSWORD_DEFAULT.
     *
     * PASSWORD_DEFAULT peut changer entre versions PHP (anciennement bcrypt).
     * PASSWORD_ARGON2I garantit un algorithme connu, résistant aux attaques GPU.
     */
    public function testControleurUtilisePasswordArgon2i(): void
    {
        $this->assertStringContainsString(
            'PASSWORD_ARGON2I',
            $this->codeSourceControleur,
            'addApiUser() doit utiliser PASSWORD_ARGON2I pour hasher les mots de passe'
        );

        // On cherche l'appel fonctionnel password_hash(... PASSWORD_DEFAULT) avec une regex
        // (pas le mot dans les commentaires)
        $this->assertDoesNotMatchRegularExpression(
            '/password_hash\s*\([^)]+PASSWORD_DEFAULT/',
            $this->codeSourceControleur,
            'password_hash() ne doit pas être appelé avec PASSWORD_DEFAULT — utiliser PASSWORD_ARGON2I'
        );
    }

    /**
     * Vérifie que le contrôleur implémente le soft delete.
     *
     * Le soft delete consiste à passer isactive à false plutôt que
     * d'exécuter un DELETE FROM. Cela préserve l'historique et les
     * données d'audit (créateur, dates, etc.).
     */
    public function testControleurImplimenteSoftDeleteSansDeletePhysique(): void
    {
        $this->assertStringContainsString(
            'setIsactive(false)',
            $this->codeSourceControleur,
            'deleteApiUser() doit utiliser setIsactive(false) pour le soft delete'
        );

        // On s'assure qu'il n'y a pas d'appel direct à ->delete() (Repository::delete = DELETE FROM)
        $this->assertStringNotContainsString(
            '->delete(',
            $this->codeSourceControleur,
            'deleteApiUser() ne doit jamais appeler ->delete() qui génère un DELETE FROM SQL'
        );
    }

    /**
     * Vérifie que le champ createdby est renseigné lors de la création.
     *
     * Ce champ d'audit indique quel admin a créé le compte.
     * Il est requis par le pattern historytable (Story 1.1).
     */
    public function testControleurRenseigneCreatedby(): void
    {
        $this->assertStringContainsString(
            'setCreatedby',
            $this->codeSourceControleur,
            'addApiUser() doit renseigner createdby avec l\'ID de l\'admin connecté'
        );
    }

    /**
     * Vérifie que le champ updatedby est renseigné lors de la modification et désactivation.
     *
     * Ce champ d'audit indique quel admin a effectué la dernière modification.
     */
    public function testControleurRenseigneUpdatedby(): void
    {
        $this->assertStringContainsString(
            'setUpdatedby',
            $this->codeSourceControleur,
            'editApiUser() et deleteApiUser() doivent renseigner updatedby avec l\'ID de l\'admin connecté'
        );
    }

    /**
     * Vérifie que le champ updatedat est renseigné lors des modifications.
     * Permet de tracer la date de la dernière mise à jour.
     */
    public function testControleurRenseigneUpdatedat(): void
    {
        $this->assertStringContainsString(
            'setUpdatedat',
            $this->codeSourceControleur,
            'editApiUser() et deleteApiUser() doivent mettre à jour le champ updatedat'
        );
    }

    /**
     * Vérifie que le contrôleur valide le format email avec filter_var.
     *
     * filter_var($email, FILTER_VALIDATE_EMAIL) est la méthode PHP standard
     * pour valider un email selon la RFC 5321/5322.
     */
    public function testControleurValideFormatEmail(): void
    {
        $this->assertStringContainsString(
            'FILTER_VALIDATE_EMAIL',
            $this->codeSourceControleur,
            'addApiUser() et editApiUser() doivent valider l\'email avec FILTER_VALIDATE_EMAIL'
        );
    }

    /**
     * Vérifie que le contrôleur utilise le format JSON standardisé.
     *
     * Le format imposé par le projet est :
     * - Succès : {"success": true, "data": {...}}   → jsonSuccess()
     * - Erreur  : {"success": false, "error": "..."} → jsonError()
     *
     * Plus de {"delete": "true"} ou {"users": [...]} non standardisés.
     */
    public function testControleurUtiliseFormatJsonStandardise(): void
    {
        $this->assertStringContainsString(
            'jsonSuccess',
            $this->codeSourceControleur,
            'Le contrôleur doit utiliser jsonSuccess() pour les réponses de succès'
        );

        $this->assertStringContainsString(
            'jsonError',
            $this->codeSourceControleur,
            'Le contrôleur doit utiliser jsonError() pour les réponses d\'erreur'
        );

        // S'assurer que l'ancien format non standardisé n'est plus utilisé
        $this->assertStringNotContainsString(
            '"delete"',
            $this->codeSourceControleur,
            'L\'ancien format {"delete":"true"} ne doit plus être utilisé'
        );
    }

    /**
     * Vérifie que les logs de debug error_log() ont été supprimés du code de production.
     *
     * Les error_log() de débogage polluent les logs serveur et peuvent exposer
     * des informations sensibles (emails, IDs) dans les fichiers de log.
     */
    public function testControleurNaPlusDeLogsDebug(): void
    {
        $this->assertStringNotContainsString(
            'error_log(',
            $this->codeSourceControleur,
            'Les appels error_log() de débogage doivent être supprimés du code de production'
        );
    }

    /**
     * Test de validation email : un email valide est accepté.
     * Ce test vérifie la fonction PHP standard directement.
     */
    public function testValidationEmailFormatCorrectEstAccepte(): void
    {
        $this->assertNotFalse(
            filter_var('test@example.com', FILTER_VALIDATE_EMAIL),
            'Un email au format valide doit passer la validation'
        );
    }

    /**
     * Test de validation email : un email sans @ est rejeté.
     */
    public function testValidationEmailSansArobaseEstRejete(): void
    {
        $this->assertFalse(
            filter_var('pasunemail', FILTER_VALIDATE_EMAIL),
            'Une chaîne sans @ ne doit pas passer la validation email'
        );
    }

    /**
     * Test de validation email : un email sans domaine est rejeté.
     */
    public function testValidationEmailSansDomainEstRejete(): void
    {
        $this->assertFalse(
            filter_var('test@', FILTER_VALIDATE_EMAIL),
            'Un email sans domaine ne doit pas passer la validation'
        );
    }

    /**
     * Vérifie que le hashage Argon2i fonctionne et que le résultat
     * peut être vérifié avec password_verify().
     *
     * C'est la chaîne complète utilisée dans addApiUser().
     */
    public function testHashageArgon2iPuisVerificationMotDePasse(): void
    {
        $motDePasseEnClair = 'MonMotDePasse@2024';

        // Simule ce que fait addApiUser()
        $hash = password_hash($motDePasseEnClair, PASSWORD_ARGON2I);

        // Vérifie que le hash commence bien par $argon2i$ (identifiant Argon2i)
        $this->assertStringStartsWith('$argon2i$', $hash, 'Le hash Argon2i doit commencer par $argon2i$');

        // Vérifie que password_verify fonctionne (comme dans Security::authenticate())
        $this->assertTrue(
            password_verify($motDePasseEnClair, $hash),
            'password_verify doit retourner true avec le bon mot de passe'
        );

        $this->assertFalse(
            password_verify('mauvaisMotDePasse', $hash),
            'password_verify doit retourner false avec un mauvais mot de passe'
        );
    }
}
