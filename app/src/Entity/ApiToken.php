<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\ActiveTrait;
use App\Repository\ApiTokenRepository;
use App\Entity\Traits\TimestampableCreatedTrait;
use App\Entity\Traits\TimestampableDeletedTrait;
use App\Entity\Traits\TimestampableUpdatedTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
#[ORM\Table(name: '`user_api_token`')]
#[UniqueEntity(fields: ['token'], message: 'Token must be unique')]
class ApiToken
{
    use ActiveTrait;
    use TimestampableCreatedTrait;
    use TimestampableUpdatedTrait;
    use TimestampableDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
