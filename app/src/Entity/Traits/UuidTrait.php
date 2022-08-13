<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait UuidTrait
{
    #[ORM\Column(name: 'pid', type: 'string', length:36, unique: true)]
    protected $pid;

    public function getPid(): string|null
    {
        return $this->pid;
    }

    public function setPid(string $pid): self
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * The Logic on how an UUID will be generated
     * @return string
     */
    public function generatePid(): string
    {
        return strtoupper(Uuid::v4()->__toString());
    }
}
