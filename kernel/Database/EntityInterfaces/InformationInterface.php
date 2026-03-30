<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface InformationInterface
{
    public function getId(): ?string;

    public function getType(): ?string;

    public function getText(): ?string;

    public function getIsread(): ?bool;

    public function getUserId(): ?string;
}
