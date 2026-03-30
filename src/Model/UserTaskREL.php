<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\UserTaskRELInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserTaskREL',
    description: 'Liaison entre un utilisateur et une tâche',
    properties: [
        new OA\Property(property: 'userId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'taskId', type: 'string', format: 'uuid'),
    ]
)]
class UserTaskREL implements UserTaskRELInterface
{
    public const TABLE = 'usertaskrel';

    private ?string $userId = null;
    private ?string $taskId = null;

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function setTaskId(?string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'taskId' => $this->taskId,
        ];
    }

    public function toDatabaseArray(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'task_id' => $this->taskId,
        ], function ($value) {
            return null !== $value;
        });
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'user_id' => 'userId',
            'task_id' => 'taskId',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
