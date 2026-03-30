<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\ClientAddressRELInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClientAddressREL',
    description: 'Liaison entre un client et une adresse',
    properties: [
        new OA\Property(property: 'siret', type: 'string', description: 'SIRET du client'),
        new OA\Property(property: 'addressId', type: 'string', format: 'uuid'),
    ]
)]
class ClientAddressREL implements ClientAddressRELInterface
{
    public const TABLE = 'clientaddressrel';

    private ?string $siret = null;
    private ?string $addressId = null;

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function getAddressId(): ?string
    {
        return $this->addressId;
    }

    public function setSiret(?string $siret): void
    {
        $this->siret = $siret;
    }

    public function setAddressId(?string $addressId): void
    {
        $this->addressId = $addressId;
    }

    public function toArray(): array
    {
        return [
            'siret' => $this->siret,
            'addressId' => $this->addressId,
        ];
    }

    public function toDatabaseArray(): array
    {
        return array_filter([
            'siret' => $this->siret,
            'address_id' => $this->addressId,
        ], function ($value) {
            return null !== $value;
        });
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'siret' => 'siret',
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
