<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Entity\Enum\ActivityType;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Entity\UserAuthActivity;
use Mmc\Security\Entity\UserAuthSession;
use Mmc\Security\User\UserInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationListener
{
    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
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

        $activity = new UserAuthActivity();
        $activity->setUserAuth($authEntity)
            ->setType(ActivityType::LOGIN)
            ;

        $session = new UserAuthSession($token->getUser()->getUsername());
        $session->setUserAuth($authEntity);

        if ($request->headers->has('user-agent')) {
            $activity->setData('user_agent', $request->headers->get('user-agent'));
            $session->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($activity);
        $this->em->persist($session);
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
