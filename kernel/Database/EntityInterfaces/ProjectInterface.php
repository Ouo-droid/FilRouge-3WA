<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface ProjectInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getDescription(): ?string;

    public function getBeginDate(): ?\DateTime;

    public function getTheoreticalDeadline(): ?\DateTime;

    public function getRealDeadline(): ?\DateTime;

    public function getEffortcalculated(): ?float;

    public function getTemplate(): ?bool;

    public function getClientId(): ?string;

    public function getProjectManagerId(): ?string;

    public function getStateId(): ?string;
}
