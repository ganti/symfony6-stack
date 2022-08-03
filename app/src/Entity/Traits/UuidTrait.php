<?php

namespace App\Entity\Traits;



use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

trait UuidTrait
{
    
    #[ORM\Column(name: 'pid', type: 'string', length:36, unique: true)]
    protected $pid;


    public function __construct()
    {
        $this->setPid();
    }

    /**
     * @return UuidInterface
     */
    public function getPid(): string
    {
        return $this->pid;
    }
    /**
     * @param uuid
     * @return Uuid
     */
    public function setPid(string $pid = null) :self
    {
        if (empty($pid))
        {
            $this->pid = strtoupper(Uuid::v4()->__toString());
        }else{
            $this->pid = $pid;
        }
        return $this;
    }
}