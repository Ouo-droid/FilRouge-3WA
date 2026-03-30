<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\ClientInterface;
use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Client',
    title: 'Client',
    description: 'Client model',
    required: ['siret', 'companyName']
)]
class Client implements ClientInterface, HistoryInterface
{
    public const TABLE = 'client';

    #[OA\Property(property: 'siret', type: 'string')]
    private ?string $siret = null;

    #[OA\Property(property: 'companyName', type: 'string')]
    private ?string $companyName = null;

    #[OA\Property(property: 'workfield', type: 'string', nullable: true)]
    private ?string $workfield = null;

    #[OA\Property(property: 'contactFirstname', type: 'string', nullable: true)]
    private ?string $contactFirstname = null;

    #[OA\Property(property: 'contactLastname', type: 'string', nullable: true)]
    private ?string $contactLastname = null;

    #[OA\Property(property: 'contactEmail', type: 'string', format: 'email', nullable: true)]
    private ?string $contactEmail = null;

    #[OA\Property(property: 'contactPhone', type: 'string', nullable: true)]
    private ?string $contactPhone = null;

    // Propriétés héritées de historytable
    private ?string $createdat = null;
    private ?string $updatedat = null;
    private ?string $createdby = null;
    private ?string $updatedby = null;
    private bool $isactive = true;

    // GETTERS
    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getWorkfield(): ?string
    {
        return $this->workfield;
    }

    public function getContactFirstname(): ?string
    {
        return $this->contactFirstname;
    }

    public function getContactLastname(): ?string
    {
        return $this->contactLastname;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    // SETTERS
    public function setSiret(?string $siret): void
    {
        $this->siret = $siret;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function setWorkfield(?string $workfield): void
    {
        $this->workfield = $workfield;
    }

    public function setContactFirstname(?string $contactFirstname): void
    {
        $this->contactFirstname = $contactFirstname;
    }

    public function setContactLastname(?string $contactLastname): void
    {
        $this->contactLastname = $contactLastname;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function setContactPhone(?string $contactPhone): void
    {
        $this->contactPhone = $contactPhone;
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
            'siret' => $this->siret,
            'companyName' => $this->companyName,
            'workfield' => $this->workfield,
            'contactFirstname' => $this->contactFirstname,
            'contactLastname' => $this->contactLastname,
            'contactEmail' => $this->contactEmail,
            'contactPhone' => $this->contactPhone,
            'isactive' => $this->isactive,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'siret' => $this->siret,
            'companyname' => $this->companyName,
            'workfield' => $this->workfield,
            'contactfirstname' => $this->contactFirstname,
            'contactlastname' => $this->contactLastname,
            'contactemail' => $this->contactEmail,
            'contactphone' => $this->contactPhone,
        ];

        return array_filter($data, function ($value) {
            return null !== $value;
        });
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'siret' => 'siret',
            'companyname' => 'companyName',
            'workfield' => 'workfield',
            'contactfirstname' => 'contactFirstname',
            'contactlastname' => 'contactLastname',
            'contactemail' => 'contactEmail',
            'contactphone' => 'contactPhone',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
