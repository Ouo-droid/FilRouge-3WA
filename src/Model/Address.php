<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\AddressInterface;
use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Address',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', readOnly: true),
        new OA\Property(property: 'streetNumber', type: 'integer', example: 12),
        new OA\Property(property: 'streetLetter', type: 'string', nullable: true, example: 'B'),
        new OA\Property(property: 'streetName', type: 'string', example: 'Rue de la Paix'),
        new OA\Property(property: 'postCode', type: 'string', example: '75001'),
        new OA\Property(property: 'state', type: 'string', nullable: true, example: 'Île-de-France'),
        new OA\Property(property: 'city', type: 'string', example: 'Paris'),
        new OA\Property(property: 'country', type: 'string', nullable: true, example: 'France'),
    ]
)]
class Address implements AddressInterface, HistoryInterface
{
    public const TABLE = 'address';

    private ?string $id = null;
    private ?int $streetNumber = null;
    private ?string $streetLetter = null;
    private ?string $streetName = null;
    private ?string $postCode = null;
    private ?string $state = null;
    private ?string $city = null;
    private ?string $country = null;

    // Propriétés héritées de historytable
    private ?string $createdat = null;
    private ?string $updatedat = null;
    private ?string $createdby = null;
    private ?string $updatedby = null;
    private bool $isactive = true;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStreetNumber(): ?int
    {
        return $this->streetNumber;
    }

    public function getStreetLetter(): ?string
    {
        return $this->streetLetter;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setStreetNumber(?int $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    public function setStreetLetter(?string $streetLetter): void
    {
        $this->streetLetter = $streetLetter;
    }

    public function setStreetName(?string $streetName): void
    {
        $this->streetName = $streetName;
    }

    public function setPostCode(?string $postCode): void
    {
        $this->postCode = $postCode;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    // GETTERS/SETTERS HistoryInterface
    public function getCreatedat(): ?string
    {
        return $this->createdat;
    }

    public function getUpdatedat(): ?string
    {
        return $this->updatedat;
    }

    public function getCreatedby(): ?string
    {
        return $this->createdby;
    }

    public function getUpdatedby(): ?string
    {
        return $this->updatedby;
    }

    public function getIsactive(): bool
    {
        return $this->isactive;
    }

    public function setCreatedat(?string $createdat): void
    {
        $this->createdat = $createdat;
    }

    public function setUpdatedat(?string $updatedat): void
    {
        $this->updatedat = $updatedat;
    }

    public function setCreatedby(?string $createdby): void
    {
        $this->createdby = $createdby;
    }

    public function setUpdatedby(?string $updatedby): void
    {
        $this->updatedby = $updatedby;
    }

    public function setIsactive(bool $isactive): void
    {
        $this->isactive = $isactive;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'streetNumber' => $this->streetNumber,
            'streetLetter' => $this->streetLetter,
            'streetName' => $this->streetName,
            'postCode' => $this->postCode,
            'state' => $this->state,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'streetnumber' => $this->streetNumber,
            'streetletter' => $this->streetLetter,
            'streetname' => $this->streetName,
            'postcode' => $this->postCode,
            'state' => $this->state,
            'city' => $this->city,
            'country' => $this->country,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, function ($value) {
            return null !== $value;
        });
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'streetnumber' => 'streetNumber',
            'streetletter' => 'streetLetter',
            'streetname' => 'streetName',
            'postcode' => 'postCode',
            'state' => 'state',
            'city' => 'city',
            'country' => 'country',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
