<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\Enum\ActivityType;
use Mmc\Security\Entity\UserAuthActivity;

class AuthenticationActivityListener
{
    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    public function onAuthenticationInteractiveSuccess(MmcAuthenticationInteractiveEvent $event)
    {
        $request = $event->getRequest();
        $authEntity = $event->getAuthEntity();
        $token = $event->getToken();

        $activity = new UserAuthActivity();
        $activity->setUserAuth($authEntity)
            ->setSessionUuid($token->getUser()->getUsername())
            ->setType(ActivityType::LOGIN)
            ;

        if ($request->headers->has('user-agent')) {
            $activity->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($activity);
    }

    public function onLogoutSuccess(MmcAuthenticationInteractiveEvent $event)
    {
        $request = $event->getRequest();
        $authEntity = $event->getAuthEntity();
        $token = $event->getToken();

        $activity = new UserAuthActivity();
        $activity->setUserAuth($authEntity)
            ->setSessionUuid($token->getUser()->getUsername())
            ->setType(ActivityType::LOGOUT)
            ;

        if ($request->headers->has('user-agent')) {
            $activity->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($activity);
    }
}
