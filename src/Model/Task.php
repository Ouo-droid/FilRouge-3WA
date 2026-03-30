<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use Kentec\Kernel\Database\EntityInterfaces\TaskInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Task',
    title: 'Task',
    description: 'Task model',
    required: ['name']
)]
class Task implements TaskInterface, HistoryInterface
{
    public const TABLE = 'task';

    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    private ?string $id = null;

    #[OA\Property(property: 'name', type: 'string')]
    private ?string $name = null;

    #[OA\Property(property: 'description', type: 'string', nullable: true)]
    private ?string $description = null;

    #[OA\Property(property: 'fieldofwork', type: 'string', nullable: true)]
    private ?string $fieldofwork = null;

    #[OA\Property(property: 'type', type: 'string', nullable: true)]
    private ?string $type = null;

    #[OA\Property(property: 'format', type: 'string', nullable: true)]
    private ?string $format = null;

    #[OA\Property(property: 'priority', type: 'string', nullable: true)]
    private ?string $priority = null;

    #[OA\Property(property: 'difficulty', type: 'string', nullable: true)]
    private ?string $difficulty = null;

    #[OA\Property(property: 'effortrequired', type: 'number', format: 'float', nullable: true)]
    private ?float $effortrequired = null;

    #[OA\Property(property: 'effortmade', type: 'number', format: 'float', nullable: true)]
    private ?float $effortmade = null;

    #[OA\Property(property: 'beginDate', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $beginDate = null;

    #[OA\Property(property: 'theoreticalEndDate', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $theoreticalEndDate = null;

    #[OA\Property(property: 'realEndDate', type: 'string', format: 'date', nullable: true)]
    private ?\DateTime $realEndDate = null;

    #[OA\Property(property: 'template', type: 'boolean', nullable: true)]
    private ?bool $template = null;

    #[OA\Property(property: 'projectId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $projectId = null;

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

    public function getFieldofwork(): ?string
    {
        return $this->fieldofwork;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function getEffortrequired(): ?float
    {
        return $this->effortrequired;
    }

    public function getEffortmade(): ?float
    {
        return $this->effortmade;
    }

    public function getBeginDate(): ?\DateTime
    {
        return $this->beginDate;
    }

    public function getTheoreticalEndDate(): ?\DateTime
    {
        return $this->theoreticalEndDate;
    }

    public function getRealEndDate(): ?\DateTime
    {
        return $this->realEndDate;
    }

    public function getTemplate(): ?bool
    {
        return $this->template;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
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

    public function setFieldofwork(?string $fieldofwork): void
    {
        $this->fieldofwork = $fieldofwork;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function setPriority(?string $priority): void
    {
        $this->priority = $priority;
    }

    public function setDifficulty(?string $difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    public function setEffortrequired(?float $effortrequired): void
    {
        $this->effortrequired = $effortrequired;
    }

    public function setEffortmade(?float $effortmade): void
    {
        $this->effortmade = $effortmade;
    }

    public function setBeginDate(?\DateTime $beginDate): void
    {
        $this->beginDate = $beginDate;
    }

    public function setTheoreticalEndDate(?\DateTime $theoreticalEndDate): void
    {
        $this->theoreticalEndDate = $theoreticalEndDate;
    }

    public function setRealEndDate(?\DateTime $realEndDate): void
    {
        $this->realEndDate = $realEndDate;
    }

    public function setTemplate(?bool $template): void
    {
        $this->template = $template;
    }

    public function setProjectId(?string $projectId): void
    {
        $this->projectId = $projectId;
    }

    public function setStateId(?string $stateId): void
    {
        $this->stateId = $stateId;
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

    /**
     * Convertit l'entité en tableau pour l'envoi au frontend (API JSON).
     *
     * NOTE : Les clés "theoricalEndDate" et "dificulty" sont gardées temporairement
     * pour compatibilité avec les Views existantes. Les collègues front devront
     * migrer vers "theoreticalEndDate" et "difficulty" puis on supprimera les anciennes clés.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'fieldofwork' => $this->fieldofwork,
            'type' => $this->type,
            'format' => $this->format,
            'priority' => $this->priority,
            'difficulty' => $this->difficulty,
            'dificulty' => $this->difficulty, // TODO(front): supprimer après migration
            'effortrequired' => $this->effortrequired,
            'effortmade' => $this->effortmade,
            'beginDate' => $this->beginDate?->format('Y-m-d'),
            'theoreticalEndDate' => $this->theoreticalEndDate?->format('Y-m-d'),
            'theoricalEndDate' => $this->theoreticalEndDate?->format('Y-m-d'), // TODO(front): supprimer après migration
            'realEndDate' => $this->realEndDate?->format('Y-m-d'),
            'template' => $this->template,
            'projectId' => $this->projectId,
            'stateId' => $this->stateId,
        ];
    }

    /**
     * Convertit l'entité en tableau pour insertion/mise à jour en BDD.
     * Les clés correspondent aux noms de colonnes SQL (snake_case, minuscules).
     */
    public function toDatabaseArray(): array
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'fieldofwork' => $this->fieldofwork,
            'type' => $this->type,
            'format' => $this->format,
            'priority' => $this->priority,
            'difficulty' => $this->difficulty,
            'effortrequired' => $this->effortrequired,
            'effortmade' => $this->effortmade,
            'begindate' => $this->beginDate?->format('Y-m-d'),
            'theoreticalenddate' => $this->theoreticalEndDate?->format('Y-m-d'),
            'realenddate' => $this->realEndDate?->format('Y-m-d'),
            'template' => $this->template,
            'project_id' => $this->projectId,
            'state_id' => $this->stateId,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, function ($value) {
            return null !== $value && '' !== $value;
        });
    }

    /**
     * Mappe les colonnes SQL (snake_case) vers les propriétés PHP (camelCase).
     * Utilisé par l'Hydrator lors de la lecture depuis la BDD.
     */
    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'begindate'          => 'beginDate',
            'theoreticalenddate' => 'theoreticalEndDate',
            'theoricalenddate'   => 'theoreticalEndDate', // alias for backward compat
            'realenddate'        => 'realEndDate',
            'project_id'         => 'projectId',
            'state_id'           => 'stateId',
        ];

        $newData = [];
        foreach ($data as $key => $value) {
            $mappedKey = $mapping[strtolower($key)] ?? $key;
            $newData[$mappedKey] = $value;
        }

        return $newData;
    }
}
