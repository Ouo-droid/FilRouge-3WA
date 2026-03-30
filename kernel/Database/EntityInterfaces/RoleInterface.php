<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface RoleInterface
{
    public function getId(): ?string;

    public function getName(): ?string;
}
