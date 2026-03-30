<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface ClientInterface
{
    public function getSiret(): ?string;

    public function getCompanyName(): ?string;

    public function getWorkfield(): ?string;

    public function getContactFirstname(): ?string;

    public function getContactLastname(): ?string;

    public function getContactEmail(): ?string;

    public function getContactPhone(): ?string;
}
