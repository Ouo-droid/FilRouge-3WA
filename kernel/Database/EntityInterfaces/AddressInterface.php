<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface AddressInterface
{
    public function getId(): ?string;

    public function getStreetNumber(): ?int;

    public function getStreetLetter(): ?string;

    public function getStreetName(): ?string;

    public function getPostCode(): ?string;

    public function getState(): ?string;

    public function getCity(): ?string;

    public function getCountry(): ?string;
}
