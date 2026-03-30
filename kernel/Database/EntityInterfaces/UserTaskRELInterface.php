<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface UserTaskRELInterface
{
    public function getUserId(): ?string;

    public function getTaskId(): ?string;
}
