<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Kentec\Kernel\Security\InputValidator;

/**
 * Tests unitaires pour InputValidator.
 *
 * Ces tests vérifient que le validateur accepte les entrées légitimes
 * et rejette correctement les entrées malformées ou dangereuses.
 */
class InputValidatorTest extends TestCase
{
    // =========================================================================
    // Tests : validateEmail()
    // =========================================================================

    /**
     * Un email au format standard doit être accepté.
     */
    public function testEmailValideEstAccepte(): void
    {
        $this->assertTrue(
            InputValidator::validateEmail('utilisateur@exemple.com'),
            'Un email standard doit être accepté'
        );
    }

    /**
     * Un email sans arobase doit être rejeté.
     * C'est la structure minimale d'un email.
     */
    public function testEmailSansArobaseEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateEmail('pasunemail'),
            'Un email sans @ doit être rejeté'
        );
    }

    /**
     * Un email sans domaine doit être rejeté.
     * "user@" n'est pas un email valide.
     */
    public function testEmailSansDomainEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateEmail('utilisateur@'),
            'Un email sans domaine doit être rejeté'
        );
    }

    /**
     * Une chaîne vide doit être rejetée comme email.
     */
    public function testEmailVideEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateEmail(''),
            'Un email vide doit être rejeté'
        );
    }

    // =========================================================================
    // Tests : validateUuid()
    // =========================================================================

    /**
     * Un UUID au format standard (avec tirets, 32 chiffres hex) doit être accepté.
     */
    public function testUuidValideEstAccepte(): void
    {
        $this->assertTrue(
            InputValidator::validateUuid('550e8400-e29b-41d4-a716-446655440000'),
            'Un UUID au format standard doit être accepté'
        );
    }

    /**
     * Un UUID en majuscules doit aussi être accepté (insensible à la casse).
     */
    public function testUuidEnMajusculesEstAccepte(): void
    {
        $this->assertTrue(
            InputValidator::validateUuid('550E8400-E29B-41D4-A716-446655440000'),
            'Un UUID en majuscules doit être accepté'
        );
    }

    /**
     * Une chaîne arbitraire ne doit pas passer la validation UUID.
     */
    public function testUuidInvalideEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateUuid('pas-un-uuid'),
            'Une chaîne non UUID doit être rejetée'
        );
    }

    /**
     * Un UUID sans tirets doit être rejeté (format non standard).
     */
    public function testUuidSansTiretsEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateUuid('550e8400e29b41d4a716446655440000'),
            'Un UUID sans tirets doit être rejeté'
        );
    }

    // =========================================================================
    // Tests : validateText()
    // =========================================================================

    /**
     * Un texte dans la plage par défaut (1-255 caractères) doit être accepté.
     */
    public function testTexteValideEstAccepte(): void
    {
        $this->assertTrue(
            InputValidator::validateText('Bonjour le monde'),
            'Un texte normal doit être accepté'
        );
    }

    /**
     * Un texte vide doit être rejeté (longueur minimale par défaut : 1).
     */
    public function testTexteVideEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateText(''),
            'Un texte vide doit être rejeté'
        );
    }

    /**
     * Un texte composé uniquement d'espaces doit être rejeté.
     * trim() en interne enlève les espaces avant le comptage.
     */
    public function testTexteEspacesSeulementEstRejete(): void
    {
        $this->assertFalse(
            InputValidator::validateText('   '),
            'Un texte composé uniquement d\'espaces doit être rejeté'
        );
    }

    /**
     * Un texte dépassant la longueur maximale doit être rejeté.
     */
    public function testTexteTropLongEstRejete(): void
    {
        $texteTropLong = str_repeat('a', 256); // 256 caractères, limite par défaut = 255

        $this->assertFalse(
            InputValidator::validateText($texteTropLong),
            'Un texte de 256 caractères doit être rejeté (limite = 255)'
        );
    }

    // =========================================================================
    // Tests : validateInteger()
    // =========================================================================

    /**
     * Un entier PHP natif dans la plage par défaut doit être accepté.
     */
    public function testEntierValideEstAccepte(): void
    {
        $this->assertTrue(
            InputValidator::validateInteger(42),
            'Un entier valide doit être accepté'
        );
    }

    /**
     * Une chaîne numérique (comme reçue d'un formulaire HTML) doit être acceptée.
     * Les données de formulaire sont TOUJOURS des chaînes en PHP.
     */
    public function testChaineNumeriqueEstAcceptee(): void
    {
        $this->assertTrue(
            InputValidator::validateInteger('42'),
            'Une chaîne numérique comme "42" doit être acceptée'
        );
    }

    /**
     * Une chaîne non numérique doit être rejetée.
     */
    public function testChaineNonNumeriqueEstRejetee(): void
    {
        $this->assertFalse(
            InputValidator::validateInteger('abc'),
            'Une chaîne non numérique doit être rejetée'
        );
    }

    /**
     * Un entier hors de la plage spécifiée doit être rejeté.
     */
    public function testEntierHorsPlagueEstRejete(): void
    {
        // On autorise uniquement les valeurs de 1 à 100
        $this->assertFalse(
            InputValidator::validateInteger(150, 1, 100),
            'Un entier hors de la plage [1-100] doit être rejeté'
        );
    }

    // =========================================================================
    // Tests : sanitizeText()
    // =========================================================================

    /**
     * Une tentative XSS avec une balise script doit être neutralisée.
     *
     * C'est le test le plus important : une attaque XSS classique est bloquée.
     */
    public function testSanitizeNeutraliseBaliseScriptXss(): void
    {
        $entreeXss = '<script>alert("XSS")</script>';

        $sortieSanitisee = InputValidator::sanitizeText($entreeXss);

        // Le résultat ne doit pas contenir de balise <script> exécutable
        $this->assertStringNotContainsString('<script>', $sortieSanitisee, 'La balise script doit être neutralisée');

        // Les caractères < et > doivent être convertis en entités HTML
        $this->assertStringContainsString('&lt;', $sortieSanitisee, 'Le < doit être converti en &lt;');
        $this->assertStringContainsString('&gt;', $sortieSanitisee, 'Le > doit être converti en &gt;');
    }

    /**
     * Les guillemets doubles doivent être échappés (pour protéger les attributs HTML).
     * Exemple : value="<?= $valeur ?>" ne doit pas être cassable avec des guillemets.
     */
    public function testSanitizeEchappeGuillemets(): void
    {
        $entreeAvecGuillemets = 'Nom avec "guillemets" et \'apostrophe\'';

        $sortieSanitisee = InputValidator::sanitizeText($entreeAvecGuillemets);

        $this->assertStringNotContainsString('"', $sortieSanitisee, 'Les guillemets doubles doivent être échappés');
        $this->assertStringNotContainsString("'", $sortieSanitisee, 'Les guillemets simples doivent être échappés');
    }

    /**
     * Un texte normal sans caractères spéciaux doit être retourné inchangé.
     * (sauf trim des espaces en début/fin)
     */
    public function testSanitizeTexteNormalInchange(): void
    {
        $texteNormal = 'Bonjour le monde';

        $sortieSanitisee = InputValidator::sanitizeText($texteNormal);

        $this->assertSame($texteNormal, $sortieSanitisee, 'Un texte sans caractères spéciaux doit rester inchangé');
    }
}
