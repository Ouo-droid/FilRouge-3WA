<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use Kentec\Kernel\Database\EntityInterfaces\ProjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Project',
    title: 'Project',
    description: 'Project model',
    required: ['name']
)]
class Project implements ProjectInterface, HistoryInterface
{
    public const TABLE = 'project';

    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    private ?string $id = null;

    #[OA\Property(property: 'name', type: 'string')]
    private ?string $name = null;

    #[OA\Property(property: 'description', type: 'string', nullable: true)]
    private ?string $description = null;

    #[OA\Property(property: 'beginDate', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $beginDate = null;

    #[OA\Property(property: 'theoreticalDeadline', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $theoreticalDeadline = null;

    #[OA\Property(property: 'realDeadline', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $realDeadline = null;

    #[OA\Property(property: 'effortcalculated', type: 'number', format: 'float', nullable: true)]
    private ?float $effortcalculated = null;

    #[OA\Property(property: 'template', type: 'boolean', nullable: true)]
    private ?bool $template = null;

    #[OA\Property(property: 'clientId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $clientId = null;

    #[OA\Property(property: 'projectManagerId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $projectManagerId = null;

    #[OA\Property(property: 'stateId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $stateId = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getBeginDate(): ?\DateTime
    {
        return $this->beginDate;
    }

    public function getTheoreticalDeadline(): ?\DateTime
    {
        return $this->theoreticalDeadline;
    }

    public function getRealDeadline(): ?\DateTime
    {
        return $this->realDeadline;
    }

    public function getEffortcalculated(): ?float
    {
        return $this->effortcalculated;
    }

    public function getTemplate(): ?bool
    {
        return $this->template;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getProjectManagerId(): ?string
    {
        return $this->projectManagerId;
    }

    public function getStateId(): ?string
    {
        return $this->stateId;
    }

    // SETTERS
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setBeginDate(?\DateTime $beginDate): void
    {
        $this->beginDate = $beginDate;
    }

    public function setTheoreticalDeadline(?\DateTime $theoreticalDeadline): void
    {
        $this->theoreticalDeadline = $theoreticalDeadline;
    }

    public function setRealDeadline(?\DateTime $realDeadline): void
    {
        $this->realDeadline = $realDeadline;
    }

    public function setEffortcalculated(?float $effortcalculated): void
    {
        $this->effortcalculated = $effortcalculated;
    }

    public function setTemplate(?bool $template): void
    {
        $this->template = $template;
    }

    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setProjectManagerId(?string $projectManagerId): void
    {
        $this->projectManagerId = $projectManagerId;
    }

    public function setStateId(?string $stateId): void
    {
        $this->stateId = $stateId;
    }

    // GETTERS/SETTERS HistoryInterface
    public function getCreatedat(): ?string { return $this->createdat; }
    public function getUpdatedat(): ?string { return $this->updatedat; }
    public function getCreatedby(): ?string { return $this->createdby; }
    public function getUpdatedby(): ?string { return $this->updatedby; }
    public function getIsactive(): bool { return $this->isactive; }

    public function setCreatedat(?string $createdat): void { $this->createdat = $createdat; }
    public function setUpdatedat(?string $updatedat): void { $this->updatedat = $updatedat; }
    public function setCreatedby(?string $createdby): void { $this->createdby = $createdby; }
    public function setUpdatedby(?string $updatedby): void { $this->updatedby = $updatedby; }
    public function setIsactive(bool $isactive): void { $this->isactive = $isactive; }

    /**
     * NOTE : La clé "theoricalDeadLine" est gardée pour compatibilité avec les Views.
     * Les collègues front devront migrer vers "theoreticalDeadline".
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'description'         => $this->description,
            'beginDate'           => $this->beginDate?->format('Y-m-d'),
            'theoreticalDeadline' => $this->theoreticalDeadline?->format('Y-m-d'),
            'theoricalDeadLine'   => $this->theoreticalDeadline?->format('Y-m-d'), // TODO(front): supprimer après migration
            'realDeadline'        => $this->realDeadline?->format('Y-m-d'),
            'effortcalculated'    => $this->effortcalculated,
            'template'            => $this->template,
            'clientId'            => $this->clientId,
            'projectManagerId'    => $this->projectManagerId,
            'stateId'             => $this->stateId,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'name'                 => $this->name,
            'description'          => $this->description,
            'begindate'            => $this->beginDate?->format('Y-m-d'),
            'theoreticaldeadline'  => $this->theoreticalDeadline?->format('Y-m-d'),
            'realdeadline'         => $this->realDeadline?->format('Y-m-d'),
            'effortcalculated'     => $this->effortcalculated,
            'template'             => $this->template,
            'client_id'            => $this->clientId,
            'project_manager_id'   => $this->projectManagerId,
            'state_id'             => $this->stateId,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, fn ($value) => null !== $value && '' !== $value);
    }

    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'begindate'           => 'beginDate',
            'theoreticaldeadline' => 'theoreticalDeadline',
            'theoricaldeadline'   => 'theoreticalDeadline', // alias for backward compat
            'realdeadline'        => 'realDeadline',
            'client_id'           => 'clientId',
            'project_manager_id'  => 'projectManagerId',
            'state_id'            => 'stateId',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
