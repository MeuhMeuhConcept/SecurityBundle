<?php

namespace Mmc\Security\Event;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\Enum\ActivityType;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Entity\UserAuthActivity;
use Mmc\Security\Entity\UserAuthSession;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationListener
{
    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $token = $event->getAuthenticationToken();

        if (!$token->getUSer() || !($auth = $token->getUser()->getAuthenticationInformation())) {
            return;
        }

        $authEntity = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => $auth->getType(),
            'key' => $auth->getKey(),
        ]);

        if (!$authEntity) {
            return;
        }

        $activity = new UserAuthActivity();
        $activity->setUserAuth($authEntity)
            ->setType(ActivityType::LOGIN)
            ;

        $session = new UserAuthSession($token->getUuid());
        $session->setUserAuth($authEntity);

        if ($request->headers->has('user-agent')) {
            $activity->setData('user_agent', $request->headers->get('user-agent'));
            $session->setData('user_agent', $request->headers->get('user-agent'));
        }

        $this->em->persist($activity);
        $this->em->persist($session);
        $this->em->flush();
    }
}
