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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationUsernamePasswordUpdater
{
    protected $em;
    protected $encoderFactory;
    protected $tokenGenerator;
    protected $eventDispatcher;
    protected $tokenStorage;
    protected $validator;

    public function __construct(
        EntityManagerInterface $em,
        EncoderFactoryInterface $encoderFactory,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->encoderFactory = $encoderFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
    }

    public function updatePassword(string $key, string $newPassword, bool $verifyOldPassword = false, string $oldPassword = '')
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
            ->setIsVerified(false)
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

    public function updateUsername(string $key, string $newUsername, bool $verifyPassword = false, string $password = '')
    {
        if (!$newUsername) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('new_username_empty');
        }

        $userAuth = $this->em->getRepository(UserAuth::class)->findOneBy([
            'type' => AuthType::USERNAME_PASSWORD,
            'key' => $key,
        ]);

        if (!$userAuth || !$userAuth->getIsEnabled()) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('user_auth_not_found');
        }

        $encoder = $this->encoderFactory->getEncoder(User::class);

        if ($verifyPassword && !$password) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('password_empty');
        }

        if ($verifyPassword && !$encoder->isPasswordValid($userAuth->getData('password'), $password, $userAuth->getData('salt'))) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('password_not_match');
        }

        $oldUsername = $userAuth->getKey();

        $userAuth
            ->setKey($newUsername)
            ->setIsVerified(false)
            ;

        $violations = $this->validator->validate($userAuth);

        if (count($violations) > 0) {
            throw new Exception\AuthenticationUsernamePasswordUpdater('username_already_used');
        }

        $token = $this->tokenStorage->getToken();
        if ($token) {
            $event = new MmcAuthenticationRelativeUserAuthEvent($token, $userAuth);
            $event->setExtra('oldUsername', $oldUsername);
            $this->eventDispatcher->dispatch($event, MmcAuthenticationEvents::AUTHENTICATION_CHANGE_USERNAME);
        }

        $this->em->persist($userAuth);
        $this->em->flush();
    }
}
