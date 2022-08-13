<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRoleRepository;
use App\Entity\Traits\ActiveTrait;
use App\Entity\Traits\TimestampableCreatedTrait;
use App\Entity\Traits\TimestampableDeletedTrait;
use App\Entity\Traits\TimestampableUpdatedTrait;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
class UserRole
{
    use ActiveTrait;
    use TimestampableCreatedTrait;
    use TimestampableUpdatedTrait;
    use TimestampableDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'role', unique: true, length: 255)]
    private ?string $role = null;

    #[ORM\Column(name: 'name', length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: 'description', length: 255)]
    private ?string $description = null;

    #[ORM\Column(name: 'is_systemrole', type: 'boolean')]
    private ?bool $systemrole = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $parentRole = null;



    public function __construct()
    {
        $this->isActive = true;
        $this->parentRole = null;
        $this->systemrole = false;
    }


    public function __toString()
    {
        return (string) $this->role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isSystemrole(): ?bool
    {
        return $this->systemrole;
    }

    public function setSystemrole(bool $systemrole): self
    {
        $this->systemrole = $systemrole;

        return $this;
    }

    public function getParentRole(): ?self
    {
        return $this->parentRole;
    }

    public function setParentRole(?self $parentRole): self
    {
        $this->parentRole = $parentRole;

        return $this;
    }

    /*
     * Recursive fetch all Parent Roles
     */
    public function getParentRoleRecursive(): ?array
    {
        $return = [];
        $parent = $this->parentRole;
        while ($parent) {
            $return[] = $parent->getRole() ;
            $parent = $parent->parentRole;
        }
        return $return;
    }

    public function getRoleAndParents(): ?array
    {
        return array_filter(array_unique(array_merge([$this->role], $this->getParentRoleRecursive())));
    }
}
