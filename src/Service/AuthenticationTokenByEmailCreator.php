<?php

namespace Mmc\Security\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mmc\Security\Entity\Enum\AuthType;
use Mmc\Security\Entity\User;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Event\MmcAuthenticationEvents;
use Mmc\Security\Event\MmcAuthenticationRelativeUserAuthEvent;
use Mmc\Security\Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationTokenByEmailCreator
{
    protected $em;
    protected $tokenGenerator;
    protected $eventDispatcher;
    protected $tokenStorage;
    protected $validator;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
    }

    public function createEmail(string $uuid, string $newEmail)
    {
        if (!$newEmail) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_empty');
        }

        $user = $this->em->getRepository(User::class)->findOneBy([
            'uuid' => $uuid,
        ]);

        foreach ($user->getAuths() as $userAuth) {
            if (AuthType::TOKEN_BY_EMAIL == $userAuth->getType()) {
                throw new Exception\AuthenticationTokenByEmailCreator('user_auth_already_exists');
            }
        }

        $userAuth = new UserAuth();
        $userAuth->setUser($user)
            ->setType(AuthType::TOKEN_BY_EMAIL)
            ->setKey($newEmail)
            ->setIsVerified(false)
            ;

        $violations = $this->validator->validate($userAuth);

        if (count($violations) > 0) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_already_used');
        }

        $token = $this->tokenStorage->getToken();
        if ($token) {
            $event = new MmcAuthenticationRelativeUserAuthEvent($token, $userAuth);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_CREATE_EMAIL);
        }

        $this->em->persist($userAuth);
        $this->em->flush();
    }
}
