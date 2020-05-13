<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\UserAuthSession;

class AuthenticationSessionListener
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
        $token = $event->getToken();

        $session = new UserAuthSession($token->getUser()->getUsername());
        $session->setUserAuth($authEntity);

        if ($request->headers->has('user-agent')) {
            $session->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($session);
    }

    public function onLogoutSuccess(MmcAuthenticationEvent $event)
    {
        $request = $event->getRequest();
        $authEntity = $event->getAuthEntity();
        $token = $event->getToken();

        $session = $this->em->getRepository(UserAuthSession::class)->findOneByUuid($token->getUser()->getUsername());

        if ($session) {
            $this->em->remove($session);
        }
    }
}
