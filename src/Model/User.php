<?php

declare(strict_types=1);

namespace Kentec\App\Model;

use Kentec\Kernel\Database\EntityInterfaces\HistoryInterface;
use Kentec\Kernel\Database\EntityInterfaces\UserInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'User model',
    required: ['email', 'firstname', 'lastname']
)]
class User implements UserInterface, HistoryInterface
{
    public const TABLE = 'users';

    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    private ?string $id = null;

    #[OA\Property(property: 'email', type: 'string', format: 'email')]
    private string $email = '';

    #[OA\Property(property: 'password', type: 'string', format: 'password')]
    private string $password = '';

    #[OA\Property(property: 'firstname', type: 'string')]
    private string $firstname = '';

    #[OA\Property(property: 'lastname', type: 'string')]
    private string $lastname = '';

    #[OA\Property(property: 'fieldofwork', type: 'string', nullable: true)]
    private ?string $fieldofwork = null;

    #[OA\Property(property: 'jobtitle', type: 'string', nullable: true)]
    private ?string $jobtitle = null;

    #[OA\Property(property: 'degree', type: 'array', items: new OA\Items(type: 'string'), nullable: true)]
    private ?array $degree = null;

    #[OA\Property(property: 'roleId', type: 'string', format: 'uuid', nullable: true)]
    private ?string $roleId = null;

    #[OA\Property(property: 'roleName', type: 'string', nullable: true)]
    private ?string $roleName = null;

    // Propriétés héritées de historytable
    private ?string $createdat = null;
    private ?string $updatedat = null;
    private ?string $createdby = null;
    private ?string $updatedby = null;
    private bool $isactive = true;

    // MÉTHODES REQUISES PAR L'INTERFACE UserInterface
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getFieldofwork(): ?string
    {
        return $this->fieldofwork;
    }

    public function getJobtitle(): ?string
    {
        return $this->jobtitle;
    }

    public function getDegree(): ?array
    {
        return $this->degree;
    }

    public function getRoleId(): ?string
    {
        return $this->roleId;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    // SETTERS
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setFieldofwork(?string $fieldofwork): void
    {
        $this->fieldofwork = $fieldofwork;
    }

    public function setJobtitle(?string $jobtitle): void
    {
        $this->jobtitle = $jobtitle;
    }

    public function setDegree(?array $degree): void
    {
        $this->degree = $degree;
    }

    public function setRoleId(?string $roleId): void
    {
        $this->roleId = $roleId;
    }

    public function setRoleName(?string $roleName): void
    {
        $this->roleName = $roleName;
    }

    // GETTERS/SETTERS HistoryInterface
    public function getCreatedat(): ?string
    {
        return $this->createdat;
    }

    public function getUpdatedat(): ?string
    {
        return $this->updatedat;
    }

    public function getCreatedby(): ?string
    {
        return $this->createdby;
    }

    public function getUpdatedby(): ?string
    {
        return $this->updatedby;
    }

    public function getIsactive(): bool
    {
        return $this->isactive;
    }

    public function setCreatedat(?string $createdat): void
    {
        $this->createdat = $createdat;
    }

    public function setUpdatedat(?string $updatedat): void
    {
        $this->updatedat = $updatedat;
    }

    public function setCreatedby(?string $createdby): void
    {
        $this->createdby = $createdby;
    }

    public function setUpdatedby(?string $updatedby): void
    {
        $this->updatedby = $updatedby;
    }

    public function setIsactive(bool $isactive): void
    {
        $this->isactive = $isactive;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'fieldofwork' => $this->fieldofwork,
            'degree' => $this->degree,
            'roleId' => $this->roleId,
            'roleName' => $this->roleName,
        ];
    }

    // Sert à faire des INSERT et UPDATE. Cette méthode génère les colonnes à écrire en DB.
    public function toDatabaseArray(): array
    {
        $data = [
            'email' => $this->email,
            'password' => $this->password,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'fieldofwork' => $this->fieldofwork,
            'jobtitle' => $this->jobtitle,
            'role_id' => $this->roleId,
        ];

        if (null !== $this->degree) {
            $data['degree'] = '{' . implode(',', array_map(function ($d) {
                return '"' . str_replace('"', '\\"', $d) . '"';
            }, $this->degree)) . '}';
        }

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        return array_filter($data, function ($value) {
            return null !== $value;
        });
    }

    /**
     * Transforme un tableau venant de la base de données en un tableau plus "propre".
     * Ex : transforme "role_id" en "roleId", et convertit les chaînes de caractères représentant des tableaux en tableaux PHP.
     *
     * @return array un tableau associatif avec les clés correspondant aux propriétés de l'entité User, prêt à être utilisé pour hydrater un objet User
     *
     * @example role_id → roleId, role_name → roleName, fieldofwork reste fieldofwork (pas de changement)
     */
    public static function fromDatabaseArray(array $data): array
    {
        $mapping = [
            'role_id' => 'roleId',
            'role_name' => 'roleName',
            'fieldofwork' => 'fieldofwork',
        ];

        foreach ($mapping as $dbKey => $attributeKey) {
            if (isset($data[$dbKey])) {
                $data[$attributeKey] = $data[$dbKey];
                if ($dbKey !== $attributeKey) {
                    unset($data[$dbKey]);
                }
            }
        }

        if (isset($data['degree']) && is_string($data['degree'])) {
            $trimmed = trim($data['degree'], '{}');
            // Paramètre escape '' requis explicitement en PHP 8.5+ (comportement par défaut modifié)
            $data['degree'] = $trimmed !== '' ? str_getcsv($trimmed, ',', '"', '') : [];
        }

        return $data;
    }
}
