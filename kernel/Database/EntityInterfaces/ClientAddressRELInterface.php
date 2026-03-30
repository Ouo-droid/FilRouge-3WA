<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface ClientAddressRELInterface
{
    public function getSiret(): ?string;

    public function getAddressId(): ?string;
}
