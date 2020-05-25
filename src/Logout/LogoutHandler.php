<?php

namespace Mmc\Security\Logout;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Event\MmcAuthenticationEvents;
use Mmc\Security\Event\MmcAuthenticationRelativeUserAuthEvent;
use Mmc\Security\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LogoutHandler implements LogoutHandlerInterface
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

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (!$token->getUser() || !$token->getUser() instanceof UserInterface) {
            return;
        }

        $authEntity = $this->em->getRepository(UserAuth::class)->findOneByUuid($token->getUser()->getUuid());

        if (!$authEntity) {
            return;
        }

        $this->eventDispatcher->dispatch(new MmcAuthenticationRelativeUserAuthEvent($token, $authEntity, $request), MmcAuthenticationEvents::LOGOUT_SUCCESS);

        $this->em->persist($authEntity);
        $this->em->flush();
    }
}
