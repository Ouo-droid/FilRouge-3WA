<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Kentec\Kernel\Security\CsrfManager;

/**
 * Tests unitaires pour CsrfManager.
 *
 * Ces tests vérifient la génération et la validation des tokens CSRF.
 * En CLI (PHPUnit), il n'y a pas de serveur web donc pas de session réelle.
 * On initialise $_SESSION manuellement comme un tableau ordinaire.
 *
 * Rappel : un token CSRF protège contre les requêtes forgées inter-sites.
 * Un attaquant ne peut pas deviner le token → la requête forgée est rejetée.
 */
class CsrfManagerTest extends TestCase
{
    /**
     * Remet $_SESSION à zéro avant chaque test pour isoler les résultats.
     * Sans ça, un token généré dans testA() serait encore là dans testB().
     */
    protected function setUp(): void
    {
        // Initialise $_SESSION comme tableau vide (simule une session PHP fraîche)
        $_SESSION = [];
    }

    /**
     * generateToken() doit créer un token non vide la première fois.
     *
     * Un token vide ne servirait à rien (n'importe qui pourrait le deviner).
     */
    public function testGenerationTokenProduireUnTokenNonVide(): void
    {
        $tokenGenere = CsrfManager::generateToken();

        $this->assertNotEmpty($tokenGenere, 'Le token CSRF généré ne doit pas être vide');
    }

    /**
     * generateToken() doit produire un token hexadécimal de 64 caractères.
     *
     * 32 octets (random_bytes(32)) convertis en hexadécimal = 64 caractères.
     * Ce format garantit 256 bits d'entropie, pratiquement impossible à deviner.
     */
    public function testGenerationTokenProduitTokenHexadecimalDe64Caracteres(): void
    {
        $tokenGenere = CsrfManager::generateToken();

        // Vérifie la longueur : 32 octets binaires → 64 caractères hexadécimaux
        $this->assertSame(64, strlen($tokenGenere), 'Le token doit avoir exactement 64 caractères');

        // Vérifie que le token ne contient que des caractères hexadécimaux (0-9, a-f)
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $tokenGenere, 'Le token doit être en hexadécimal');
    }

    /**
     * generateToken() doit retourner le MÊME token si appelé deux fois de suite.
     *
     * On ne régénère pas un nouveau token à chaque appel : cela casserait la navigation
     * multi-onglets (l'onglet A invaliderait le token de l'onglet B).
     * Un token par session suffit.
     */
    public function testGenerationTokenRetourneMemeTokenSiDejaEnSession(): void
    {
        $premierToken  = CsrfManager::generateToken();
        $deuxiemeToken = CsrfManager::generateToken();

        $this->assertSame($premierToken, $deuxiemeToken, 'Le même token doit être retourné pour la même session');
    }

    /**
     * validateToken() doit accepter un token valide.
     *
     * C'est le cas nominal : l'utilisateur a soumis le formulaire avec le bon token.
     */
    public function testValidationTokenValideEstAccepte(): void
    {
        // On génère un token et on simule sa soumission dans le formulaire
        $tokenGenere = CsrfManager::generateToken();

        $resultatValidation = CsrfManager::validateToken($tokenGenere);

        $this->assertTrue($resultatValidation, 'Un token correct doit être accepté');
    }

    /**
     * validateToken() doit rejeter un token invalide (mauvaise valeur).
     *
     * Simule un attaquant qui envoie un token aléatoire ou forgé.
     */
    public function testValidationTokenInvalideEstRejete(): void
    {
        // On génère un vrai token en session
        CsrfManager::generateToken();

        // L'attaquant envoie un token forgé
        $tokenForge = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

        $resultatValidation = CsrfManager::validateToken($tokenForge);

        $this->assertFalse($resultatValidation, 'Un token forgé doit être rejeté');
    }

    /**
     * validateToken() doit rejeter un token vide.
     *
     * Simule une requête forgée sans token (attaque basique sans token CSRF).
     */
    public function testValidationTokenVideEstRejete(): void
    {
        // On génère un vrai token en session
        CsrfManager::generateToken();

        $resultatValidation = CsrfManager::validateToken('');

        $this->assertFalse($resultatValidation, 'Un token vide doit être rejeté');
    }

    /**
     * invalidateToken() doit supprimer le token de la session.
     *
     * Après invalidation, validateToken() doit rejeter l'ancien token.
     * Cela force la régénération d'un nouveau token pour le prochain formulaire.
     */
    public function testInvalidationTokenLeSupprimeDeLaSession(): void
    {
        // On génère un token
        $ancienToken = CsrfManager::generateToken();

        // On l'invalide
        CsrfManager::invalidateToken();

        // L'ancien token ne doit plus être accepté
        $resultatValidation = CsrfManager::validateToken($ancienToken);

        $this->assertFalse($resultatValidation, 'Après invalidation, l\'ancien token ne doit plus être accepté');
    }

    /**
     * Après invalidation, generateToken() crée un NOUVEAU token différent.
     *
     * Ce comportement garantit qu'après une action sensible (ex: soumission de formulaire),
     * le prochain formulaire aura un token frais et différent.
     */
    public function testApresInvalidationNouveauTokenEstDifferent(): void
    {
        $ancienToken = CsrfManager::generateToken();
        CsrfManager::invalidateToken();
        $nouveauToken = CsrfManager::generateToken();

        $this->assertNotSame($ancienToken, $nouveauToken, 'Après invalidation, un nouveau token différent doit être généré');
    }
}
