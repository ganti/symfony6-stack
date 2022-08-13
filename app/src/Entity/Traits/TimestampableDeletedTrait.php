<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

trait TimestampableDeletedTrait
{
    use SoftDeleteableEntity;
    /**
     * @var \DateTime|null
    */
    #[ORM\Column(name: 'deleted', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $deletedAt;

    /**
     * Get deletedAt
     * @return datetime
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * Set or clear the deleted at timestamp.
     * @return self
     */
    public function setDeletedAt(?\DateTimeInterface  $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Check if the entity has been soft deleted.
     * @return bool
     */
    public function isDeleted()
    {
        return null !== $this->deletedAt;
    }
}
