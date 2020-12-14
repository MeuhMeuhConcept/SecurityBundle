<?php

namespace Mmc\Security\Logout;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Event\MmcAuthenticationEvents;
use Mmc\Security\Event\MmcAuthenticationRelativeUserAuthEvent;
use Mmc\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LogoutListener implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return ['logout' => 'logout'];
    }

    public function logout(LogoutEvent $event)
    {
        if (!$event->getToken()->getUser() || !$event->getToken()->getUser() instanceof UserInterface) {
            return;
        }

        $authEntity = $this->em->getRepository(UserAuth::class)->findOneByUuid($event->getToken()->getUser()->getUuid());

        if (!$authEntity) {
            return;
        }

        $this->eventDispatcher->dispatch(new MmcAuthenticationRelativeUserAuthEvent($event->getToken(), $authEntity, $event->getRequest()), MmcAuthenticationEvents::LOGOUT_SUCCESS);

        $this->em->persist($authEntity);
        $this->em->flush();

        $event->setResponse(new Response(null, 204));
    }
}
