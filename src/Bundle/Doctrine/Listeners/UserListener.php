<?php

namespace Mmc\Security\Bundle\Doctrine\Listeners;

use Mmc\Security\Component\Model\UserInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        if (!$object instanceof UserInterface) {
            return;
        }

        $object->setUuid(uuid_create(UUID_TYPE_RANDOM));

        if ($object->isEnabled() == null) {
            $object->setEnable(true);
        }
    }
}
