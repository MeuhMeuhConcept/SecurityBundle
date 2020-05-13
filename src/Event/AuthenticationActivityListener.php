<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\Enum\ActivityType;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Entity\UserAuthActivity;

class AuthenticationActivityListener
{
    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    public function onAuthenticationSuccess(MmcAuthenticationEvent $event)
    {
        $request = $event->getRequest();
        $authEntity = $event->getAuthEntity();

        $activity = new UserAuthActivity();
        $activity->setUserAuth($authEntity)
            ->setType(ActivityType::LOGIN)
            ;

        if ($request->headers->has('user-agent')) {
            $activity->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($activity);
    }
}
