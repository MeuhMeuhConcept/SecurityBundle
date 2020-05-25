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

class AuthenticationUsernamePasswordUpdater
{
    protected $em;
    protected $encoderFactory;
    protected $tokenGenerator;
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

    public function update(string $key, string $newPassword, bool $verifyOldPassword = false, string $oldPassword = '')
    {
        if (!$newPassword) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('new_password_empty');
        }

        $userAuth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::USERNAME_PASSWORD,
            'key' => $key,
        ]);

        if (!$userAuth || !$userAuth->getIsEnabled()) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('user_auth_not_found');
        }

        $encoder = $this->encoderFactory->getEncoder(User::class);

        if ($verifyOldPassword && !$oldPassword) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('old_password_empty');
        }

        if ($verifyOldPassword && !$encoder->isPasswordValid($userAuth->getData('password'), $oldPassword, $userAuth->getData('salt'))) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('old_password_not_match');
        }

        $passwordEncoded = $encoder->encodePassword($newPassword, null);
        $oldPasswordEncoded = $userAuth->getData('password');

        $userAuth
            ->setData('password', $passwordEncoded)
            ;

        $token = $this->tokenStorage->getToken();
        if ($token) {
            $event = new MmcAuthenticationRelativeUserAuthEvent($token, $userAuth);
            $event->setExtra('oldPassword', $oldPasswordEncoded);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_CHANGE_PASSWORD);
        }

        $this->em->persist($userAuth);
        $this->em->flush();
    }
}
