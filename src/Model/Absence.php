<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Absence',
    title: 'Absence',
    description: 'Absence model',
    required: ['user_id', 'startdate', 'enddate']
)]
class Absence
{
    public const TABLE = 'absence';

    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    private ?string $id = null;

    #[OA\Property(property: 'user_id', type: 'string', format: 'uuid')]
    private ?string $user_id = null;

    #[OA\Property(property: 'reason', type: 'string', nullable: true)]
    private ?string $reason = null;

    #[OA\Property(property: 'startdate', type: 'string', format: 'date')]
    private ?string $startdate = null;

    #[OA\Property(property: 'enddate', type: 'string', format: 'date')]
    private ?string $enddate = null;

    #[OA\Property(property: 'createdat', type: 'string', format: 'date-time')]
    private ?string $createdat = null;

    // ── Getters ──────────────────────────────────────────────────────────────

    public function getId(): ?string { return $this->id; }
    public function getUserId(): ?string { return $this->user_id; }
    public function getReason(): ?string { return $this->reason; }
    public function getStartdate(): ?string { return $this->startdate; }
    public function getEnddate(): ?string { return $this->enddate; }
    public function getCreatedat(): ?string { return $this->createdat; }

    // ── Setters ──────────────────────────────────────────────────────────────

    public function setId(?string $id): void { $this->id = $id; }
    public function setUserId(?string $user_id): void { $this->user_id = $user_id; }
    public function setReason(?string $reason): void { $this->reason = $reason; }
    public function setStartdate(?string $startdate): void { $this->startdate = $startdate; }
    public function setEnddate(?string $enddate): void { $this->enddate = $enddate; }
    public function setCreatedat(?string $createdat): void { $this->createdat = $createdat; }

    // ── Serialization ────────────────────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'user_id'   => $this->user_id,
            'reason'    => $this->reason,
            'startdate' => $this->startdate,
            'enddate'   => $this->enddate,
            'createdat' => $this->createdat,
        ];
    }

    public function toDatabaseArray(): array
    {
        $data = [
            'user_id'   => $this->user_id,
            'reason'    => $this->reason,
            'startdate' => $this->startdate,
            'enddate'   => $this->enddate,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, fn ($v) => null !== $v);
    }
}
