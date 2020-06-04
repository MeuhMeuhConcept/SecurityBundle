<?php

namespace Mmc\Security\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mmc\Security\Entity\Enum\AuthType;
use Mmc\Security\Entity\UserAuth;
use Mmc\Security\Event\MmcAuthenticationEvents;
use Mmc\Security\Event\MmcAuthenticationRelativeUserAuthEvent;
use Mmc\Security\Exception;
use Mmc\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationTokenByEmailVerifier
{
    protected $em;
    protected $encoderFactory;
    protected $eventDispatcher;
    protected $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        EncoderFactoryInterface $encoderFactory,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->encoderFactory = $encoderFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    public function verify(string $key, string $token): string
    {
        $userAuth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::TOKEN_BY_EMAIL,
            'key' => $key,
        ]);

        if (!$userAuth || !$userAuth->getIsEnabled()) {
            throw new Exception\AuthenticationTokenByEmailVerifier('user_auth_not_found');
        }

        if ($userAuth->getIsVerified()) {
            throw new Exception\AuthenticationTokenByEmailVerifier('user_auth_already_verified');
        }

        $now = new \DatetimeImmutable();

        if ($userAuth->getData('expired_at')) {
            if ($now > new \Datetime($userAuth->getData('expired_at'))) {
                throw new Exception\AuthenticationTokenByEmailVerifier('too_late');
            }
        }

        $encoder = $this->encoderFactory->getEncoder(User::class);

        if (!$encoder->isPasswordValid($userAuth->getData('password'), $token, null)) {
            throw new Exception\AuthenticationTokenByEmailVerifier('token_not_match');
        }

        $userAuth
            ->setIsVerified(true)
            ->setData('password', '')
            ;

        $sessionToken = $this->tokenStorage->getToken();
        if ($sessionToken) {
            $event = new MmcAuthenticationRelativeUserAuthEvent($sessionToken, $userAuth);
            $event->setExtra('token', $token);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_TOKEN_BY_EMAIL_HAS_BEEN_VERIFIED);
        }

        $this->em->persist($userAuth);
        $this->em->flush();

        return $token;
    }
}
