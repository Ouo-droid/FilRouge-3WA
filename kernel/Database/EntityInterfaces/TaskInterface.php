<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface TaskInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getDescription(): ?string;

    public function getFieldofwork(): ?string;

    public function getType(): ?string;

    public function getFormat(): ?string;

    public function getPriority(): ?string;

    public function getDifficulty(): ?string;

    public function getEffortrequired(): ?float;

    public function getEffortmade(): ?float;

    public function getBeginDate(): ?\DateTime;

    public function getTheoreticalEndDate(): ?\DateTime;

    public function getRealEndDate(): ?\DateTime;

    public function getTemplate(): ?bool;

    public function getProjectId(): ?string;

    public function getStateId(): ?string;
}
