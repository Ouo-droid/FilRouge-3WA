<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\UserAddressRELInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserAddressREL',
    description: 'Liaison entre un utilisateur et une adresse',
    properties: [
        new OA\Property(property: 'userId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'addressId', type: 'string', format: 'uuid'),
    ]
)]
class UserAddressREL implements UserAddressRELInterface
{
    public const TABLE = 'useraddressrel';

    private ?string $userId = null;
    private ?string $addressId = null;

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getAddressId(): ?string
    {
        return $this->addressId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function setAddressId(?string $addressId): void
    {
        $this->addressId = $addressId;
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'addressId' => $this->addressId,
        ];
    }

    public function toDatabaseArray(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
        ], function ($value) {
            return null !== $value;
        });
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'user_id' => 'userId',
            'address_id' => 'addressId',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
