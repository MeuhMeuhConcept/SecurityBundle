<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\UserAuthSession;
use Mmc\Security\Service\SessionTTLProvider;

class AuthenticationSessionListener
{
    protected $em;
    protected $sessionTTLProvider;

    public function __construct(
        EntityManager $em,
        SessionTTLProvider $sessionTTLProvider
    ) {
        $this->em = $em;
        $this->sessionTTLProvider = $sessionTTLProvider;
    }

    public function onAuthenticationInteractiveSuccess(MmcAuthenticationRelativeUserAuthEvent $event)
    {
        $request = $event->getRequest();
        $authEntity = $event->getAuthEntity();
        $token = $event->getToken();

        $session = new UserAuthSession($token->getUser()->getUsername());
        $session->setUserAuth($authEntity);

        if ($request && $request->headers->has('user-agent')) {
            $session->setData('user_agent', $request->headers->get('user-agent'));
        }

        $ttl = $this->sessionTTLProvider->getSessionTTL($token->getUser()->getType());

        if (!$token->hasExtra('rememberMe') || !$token->getExtra('rememberMe')) {
            $now = new \DatetimeImmutable();
            $expiredAt = $now->modify('+'.$ttl.' seconds');
            $session->setExpiredAt($expiredAt);
        }

        $this->em->persist($session);
    }

    public function onAuthenticationSuccess(MmcAuthenticationEvent $event)
    {
        $token = $event->getToken();

        $ttl = $this->sessionTTLProvider->getSessionTTL($token->getUser()->getType());

        $now = new \DatetimeImmutable();
        $expiredAt = $now->modify('+'.$ttl.' seconds');

        $qb = $this->em->getRepository(UserAuthSession::class)->createQueryBuilder('s')
            ->where('s.uuid = :uuid')
            ->setParameter('uuid', $token->getUser()->getUsername())
            ->update()
            ->set('s.updatedAt', ':now')
            ->setParameter('now', $now)
            ->set('s.expiredAt', 'CAST(CASE WHEN s.expiredAt IS NULL THEN :null ELSE :expiredAt END as timestamp)')
            ->setParameter('null', null)
            ->setParameter('expiredAt', $expiredAt)
            ;

        $qb->getQuery()->execute();
    }

    public function onLogoutSuccess(MmcAuthenticationRelativeUserAuthEvent $event)
    {
        $authEntity = $event->getAuthEntity();
        $token = $event->getToken();

        $session = $this->em->getRepository(UserAuthSession::class)->findOneByUuid($token->getUser()->getUsername());

        if ($session) {
            $session->setExpiredAt(new \Datetime());
            $this->em->persist($session);
        }
    }
}
