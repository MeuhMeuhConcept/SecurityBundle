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

class AuthenticationTokenByEmailRefresher
{
    protected $em;
    protected $encoderFactory;
    protected $tokenGenerator;
    protected $minimalRefreshInterval;
    protected $lifetime;
    protected $eventDispatcher;
    protected $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        EncoderFactoryInterface $encoderFactory,
        TokenGenerator $tokenGenerator,
        $minimalRefreshInterval,
        $lifetime,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->encoderFactory = $encoderFactory;
        $this->tokenGenerator = $tokenGenerator;
        $this->minimalRefreshInterval = $minimalRefreshInterval;
        $this->lifetime = $lifetime;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    public function refresh(string $key): string
    {
        $userAuth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::TOKEN_BY_EMAIL,
            'key' => $key,
        ]);

        if (!$userAuth || !$userAuth->getIsEnabled()) {
            throw new Exception\AuthenticationTokenByEmailRefresher('user_auth_not_found');
        }

        $now = new \DatetimeImmutable();
        $limitGeneratedAt = $now->modify('-'.$this->minimalRefreshInterval);

        if ($userAuth->getData('generated_at')) {
            $generatedAt = new \Datetime($userAuth->getData('generated_at'));

            if ($generatedAt > $limitGeneratedAt) {
                throw new Exception\AuthenticationTokenByEmailRefresher('too_soon');
            }
        }

        $encoder = $this->encoderFactory->getEncoder(User::class);

        $token = $this->tokenGenerator->generate();

        $passwordEncoded = $encoder->encodePassword($token, null);

        $userAuth
            ->setData('password', $passwordEncoded)
            ->setData('generated_at', $now->format('Y-m-d H:i:s'))
            ->setData('expired_at', $now->modify('+'.$this->lifetime)->format('Y-m-d H:i:s'))
            ;

        $sessionToken = $this->tokenStorage->getToken();
        if ($sessionToken) {
            $event = new MmcAuthenticationRelativeUserAuthEvent($sessionToken, $userAuth);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_REFRESH_TOKEN_BY_EMAIL);
        }

        $this->em->persist($userAuth);
        $this->em->flush();

        return $token;
    }
}
