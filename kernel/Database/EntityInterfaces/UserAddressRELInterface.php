<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface UserAddressRELInterface
{
    public function getUserId(): ?string;

    public function getAddressId(): ?string;
}
