<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityUUIDListener
{
    public function __construct()
    {
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();


        if (is_callable([$entity, 'generatePid'])) {
            $entity->setPid($entity->generatePid());
        }
    }
}
