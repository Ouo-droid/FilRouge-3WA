<?php

declare(strict_types=1);

namespace Kentec\Kernel\Database\EntityInterfaces;

interface UserInterface
{
    public function getId(): ?string;

    public function getEmail(): string;

    public function getPassword(): string;

    public function getUsername(): string;

    public function getFirstname(): string;

    public function getLastname(): string;

    public function getFieldofwork(): ?string;

    public function getDegree(): ?array;

    public function getRoleId(): ?string;

    public function getRoleName(): ?string;

    public function setRoleName(?string $roleName): void;
}
