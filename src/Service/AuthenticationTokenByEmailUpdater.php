<?php

namespace Mmc\Security\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mmc\Security\Entity\Enum\AuthType;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Event\MmcAuthenticationEvents;
use Mmc\Security\Event\MmcAuthenticationRelativeUserAuthEvent;
use Mmc\Security\Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationTokenByEmailUpdater
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

    public function updateEmail(string $key, string $newEmail)
    {
        if (!$newEmail) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_empty');
        }

        $userAuth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::TOKEN_BY_EMAIL,
            'key' => $key,
        ]);

        if (!$userAuth || !$userAuth->getIsEnabled()) {
            throw new Exception\AuthenticationTokenByEmailUpdater('user_auth_not_found');
        }

        if ($newEmail == $userAuth->getKey()) {
            throw new Exception\AuthenticationTokenByEmailUpdater('email_identical');
        }

        $oldEmail = $userAuth->getKey();

        $userAuth
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
            $event->setExtra('oldEmail', $oldEmail);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_CHANGE_EMAIL);
        }

        $this->em->persist($userAuth);
        $this->em->flush();
    }
}
