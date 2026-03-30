<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Kernel\Utils;

use Kentec\Kernel\Security\JwtManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la classe JwtManager.
 *
 * On vérifie les 4 cas critiques :
 *  - Token valide → doit être accepté
 *  - Token expiré → doit être rejeté
 *  - Token malformé (pas 3 segments) → doit être rejeté
 *  - Token avec signature altérée → doit être rejeté
 *  - Décodage du payload → les données doivent être retrouvées
 */
class JwtManagerTest extends TestCase
{
    private JwtManager $jwtManager;
    private string $testSecret = 'cle_secrete_pour_tests_unitaires';

    protected function setUp(): void
    {
        $this->jwtManager = new JwtManager($this->testSecret);
    }

    /**
     * Un token créé à l'instant avec une expiration future doit être valide.
     */
    public function testTokenValideEstAccepte(): void
    {
        $payload = [
            'user_id' => 'abc-123',
            'role'    => 'ADMIN',
            'iat'     => time(),
            'exp'     => time() + 3600, // expire dans 1h
        ];

        $token = $this->jwtManager->createToken($payload);

        $this->assertNotEmpty($token);
        $this->assertTrue($this->jwtManager->validateToken($token));
    }

    /**
     * Un token dont le champ 'exp' est dans le passé doit être rejeté.
     */
    public function testTokenExpireEstRejete(): void
    {
        $payload = [
            'user_id' => 'abc-123',
            'exp'     => time() - 1, // expiré il y a 1 seconde
        ];

        $token = $this->jwtManager->createToken($payload);

        $this->assertFalse($this->jwtManager->validateToken($token));
    }

    /**
     * Un token sans champ 'exp' doit être rejeté (on refuse les tokens sans expiration).
     */
    public function testTokenSansExpirationEstRejete(): void
    {
        $payload = ['user_id' => 'abc-123']; // pas de 'exp'

        $token = $this->jwtManager->createToken($payload);

        $this->assertFalse($this->jwtManager->validateToken($token));
    }

    /**
     * Une chaîne ne ressemblant pas à un JWT (moins de 3 segments) doit être rejetée.
     */
    public function testTokenMalformeEstRejete(): void
    {
        $this->assertFalse($this->jwtManager->validateToken('pas.un.jwt.valide.du.tout'));
        $this->assertFalse($this->jwtManager->validateToken('seulementunsegment'));
        $this->assertFalse($this->jwtManager->validateToken('deux.segments'));
    }

    /**
     * Si la signature du token est modifiée (token falsifié), il doit être rejeté.
     */
    public function testTokenAvecSignatureAltereeEstRejete(): void
    {
        $payload = ['user_id' => 'abc-123', 'exp' => time() + 3600];
        $token   = $this->jwtManager->createToken($payload);

        // Remplacer le 3ème segment (signature) par une valeur arbitraire
        $segments    = explode('.', $token);
        $segments[2] = 'signaturefalsemodifiee';
        $alteredToken = implode('.', $segments);

        $this->assertFalse($this->jwtManager->validateToken($alteredToken));
    }

    /**
     * decodeToken() doit retourner les données du payload telles qu'elles ont été encodées.
     */
    public function testDecodeTokenRetourneLePayload(): void
    {
        $payload = ['user_id' => 'xyz-789', 'role' => 'USER', 'exp' => time() + 3600];
        $token   = $this->jwtManager->createToken($payload);

        $decoded = $this->jwtManager->decodeToken($token);

        $this->assertEquals('xyz-789', $decoded['user_id']);
        $this->assertEquals('USER', $decoded['role']);
    }

    /**
     * Un token signé avec un secret différent doit être rejeté.
     */
    public function testTokenSigneAvecAutreSecretEstRejete(): void
    {
        $autreJwtManager = new JwtManager('autre_cle_secrete_differente');
        $payload = ['user_id' => 'abc-123', 'exp' => time() + 3600];

        // Token créé par l'autre manager (avec une autre clé)
        $tokenAutreSecret = $autreJwtManager->createToken($payload);

        // Validé par notre manager → doit échouer car clés différentes
        $this->assertFalse($this->jwtManager->validateToken($tokenAutreSecret));
    }
}
