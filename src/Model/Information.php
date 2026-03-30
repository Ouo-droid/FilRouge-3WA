<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use Kentec\Kernel\Database\EntityInterfaces\InformationInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Information',
    title: 'Information',
    description: 'Information model',
    required: ['type', 'text']
)]
class Information implements InformationInterface, HistoryInterface
{
    public const TABLE = 'information';

    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    private ?string $id = null;

    #[OA\Property(property: 'type', type: 'string')]
    private ?string $type = null;

    #[OA\Property(property: 'text', type: 'string')]
    private ?string $text = null;

    #[OA\Property(property: 'isread', type: 'boolean', nullable: true)]
    private ?bool $isread = null;

    #[OA\Property(property: 'userId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $userId = null;

    // Propriétés héritées de historytable
    private ?string $createdat = null;
    private ?string $updatedat = null;
    private ?string $createdby = null;
    private ?string $updatedby = null;
    private bool $isactive = true;

    // GETTERS
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getIsread(): ?bool
    {
        return $this->isread;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    // SETTERS
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function setIsread(?bool $isread): void
    {
        $this->isread = $isread;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
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
            'type' => $this->type,
            'text' => $this->text,
            'isread' => $this->isread,
            'userId' => $this->userId,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'type' => $this->type,
            'text' => $this->text,
            'isread' => $this->isread,
            'user_id' => $this->userId,
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
            'user_id' => 'userId',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
