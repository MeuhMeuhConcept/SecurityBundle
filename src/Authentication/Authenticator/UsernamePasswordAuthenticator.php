<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token \MmcToken;
use Mmc\Security\User\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class UsernamePasswordAuthenticator implements AuthenticatorInterface
{
    protected $encoderFactory;

    public function __construct(
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->encoderFactory = $encoderFactory;
    }

    public function supports(MmcToken $token): bool
    {
        return 'username_password' == $token->getType();
    }

    public function authenticate(MmcToken $token, User $user): bool
    {
        if (!$user || !$user->getData('password')) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        $password = $token->getExtra('password');

        if (!$password) {
            return false;
        }

        if (!$encoder->isPasswordValid($user->getData('password'), $password, $user->getData('salt'))) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        return true;
    }
}
