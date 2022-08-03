<?php

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TimestampableCreatedTrait;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    use TimestampableCreatedTrait;

    public const ALLOWED_LEVELS = ['ERROR', 'WARNING', 'INFO', 'NOTICE', 'DEBUG', 'NO_LEVEL'];

    public function __construct()
    {
        $this->subcontext = '';
    }

    public function __toString()
    {
        return (string) $this->message;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'level', length: 255)]
    private ?string $level = null;

    #[ORM\Column(name: 'context', length: 255)]
    private ?string $context = null;

    #[ORM\Column(name: 'subcontext', length: 255)]
    private ?string $subcontext = null;

    #[ORM\Column(name: 'message', type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(name: 'is_success', nullable: true)]
    private ?bool $success = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?User $user = null;

    #[ORM\Column(name: 'clientIP', length: 255, nullable: true)]
    private ?string $clientIP = null;

    #[ORM\Column(name: 'clientLocale', length: 20, nullable: true)]
    private ?string $clientLocale = null;

    #[ORM\Column(name: 'requestMethod', length: 255, nullable: true)]
    private ?string $requestMethod = null;

    #[ORM\Column(name: 'requestPath', type: Types::TEXT, nullable: true)]
    private ?string $requestPath = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $level = strtoupper($level);
        if (in_array($level, self::ALLOWED_LEVELS)) {
            $this->level = $level;
        } else {
            $this->level = 'NO_LEVEL';
        }
        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getSubcontext(): ?string
    {
        return $this->subcontext;
    }

    public function setSubcontext(string $subcontext): self
    {
        $this->subcontext = $subcontext;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

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

    public function getClientIP(): ?string
    {
        return $this->clientIP;
    }

    public function setClientIP(?string $clientIP): self
    {
        $this->clientIP = $clientIP;

        return $this;
    }

    public function getClientLocale(): ?string
    {
        return $this->clientLocale;
    }

    public function setClientLocale(?string $clientLocale): self
    {
        $this->clientLocale = $clientLocale;

        return $this;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $requestMethod): self
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }

    public function setRequestPath(?string $requestPath): self
    {
        $this->requestPath = $requestPath;

        return $this;
    }
}
