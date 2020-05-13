<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Entity\UserAuthSession;
use Mmc\Security\User\UserInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationListener
{
    protected $em;
    protected $eventDispatcher;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $token = $event->getAuthenticationToken();

        if (!$token instanceof MmcToken || !$token->getUser() || !$token->getUser() instanceof UserInterface) {
            return;
        }

        $authEntity = $this->em->getRepository(UserAuth::class)->findOneByUuid($token->getUser()->getUuid());

        if (!$authEntity) {
            return;
        }

        $authEntity->setIsVerified(true);

        $this->eventDispatcher->dispatch(new MmcAuthenticationEvent($token, $authEntity, $request), MmcAuthenticationEvents::AUTHENTICATION_SUCCESS);

        $this->em->persist($authEntity);
        $this->em->flush();
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if (!$token || !$token->getUser() || !$token->getUser() instanceof UserInterface) {
            return;
        }

        $qb = $this->em->getRepository(UserAuthSession::class)->createQueryBuilder('s')
            ->where('s.uuid = :uuid')
            ->setParameter('uuid', $token->getUser()->getUsername())
            ->update()
            ->set('s.updatedAt', ':now')
            ->setParameter('now', new \Datetime())
            ;

        $qb->getQuery()->execute();
    }
}
