<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\RoleInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Role',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', readOnly: true),
        new OA\Property(property: 'name', type: 'string', example: 'ADMIN'),
    ]
)]
class Role implements RoleInterface
{
    public const TABLE = 'role';

    private ?string $id = null;
    private ?string $name = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'name' => $this->name,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, function ($value) {
            return null !== $value;
        });
    }
}
