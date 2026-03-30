<?php

declare(strict_types=1);

namespace Kentec\Kernel\Security;

/**
 * Gère la création, la validation et le décodage de tokens JWT.
 *
 * Un JWT = 3 parties en Base64URL séparées par des points : header.payload.signature
 * Il est signé (non falsifiable) mais pas chiffré (lisible par tous).
 * Ne jamais y stocker de mot de passe ou donnée ultra-sensible.
 */
class JwtManager
{
    /** @var string Clé secrète HMAC (définie dans .env sous JWT_SECRET) */
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Crée un token JWT signé à partir d'un payload.
     * Le payload doit contenir 'exp' (timestamp d'expiration Unix).
     *
     * @param array<string, mixed> $payload
     */
    public function createToken(array $payload): string
    {
        $encodedHeader  = $this->base64UrlEncode((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload));

        // Signature HMAC-SHA256 sur "header.payload" — le 4ème param `true` donne les bytes bruts
        $rawSignature     = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->secretKey, true);
        $encodedSignature = $this->base64UrlEncode($rawSignature);

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }

    /**
     * Valide un token JWT : vérifie le format (3 segments), la signature et l'expiration.
     * Retourne false si l'une des trois vérifications échoue.
     */
    public function validateToken(string $token): bool
    {
        // 1. Format : exactement 3 segments séparés par des points
        $segments = explode('.', $token);
        if (\count($segments) !== 3) {
            return false;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;

        // 2. Signature : recalcul et comparaison en temps constant (résistant aux timing attacks)
        $receivedSignature = $this->base64UrlDecode($encodedSignature);
        $expectedSignature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->secretKey, true);

        if (!hash_equals($receivedSignature, $expectedSignature)) {
            return false;
        }

        // 3. Expiration : 'exp' doit exister et être dans le futur
        $payload = $this->decodeToken($token);

        if (!isset($payload['exp'])) {
            return false;
        }

        if (time() >= (int) $payload['exp']) {
            return false;
        }

        return true;
    }

    /**
     * Décode le payload d'un token sans vérifier la signature.
     * TOUJOURS appeler validateToken() avant d'utiliser ces données.
     *
     * @return array<string, mixed>
     */
    public function decodeToken(string $token): array
    {
        $segments = explode('.', $token);
        $encodedPayload = $segments[1] ?? '';
        $rawJson = $this->base64UrlDecode($encodedPayload);

        return json_decode($rawJson, true) ?? [];
    }

    /**
     * Encode en Base64URL (variante URL-safe : +→-, /→_, sans padding =).
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Décode depuis Base64URL en remettant les caractères standard et le padding.
     */
    private function base64UrlDecode(string $data): string
    {
        $base64 = strtr($data, '-_', '+/');
        $padding = (4 - \strlen($base64) % 4) % 4;

        return (string) base64_decode($base64 . str_repeat('=', $padding));
    }
}
