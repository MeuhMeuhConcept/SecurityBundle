<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token \MmcToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class UsernamePasswordAuthenticator implements AuthenticatorInterface
{
    protected $encoderFactory;

    public function __construct(
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->encoderFactory = $encoderFactory;
    }

    public function supports(MmcToken $token, UserInterface $user): bool
    {
        return 'username_password' == $token->getType();
    }

    public function authenticate(MmcToken $token, UserInterface $user): bool
    {
        if (!$user || !$user->getPassword()) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        $password = $token->getExtra('password');

        if (!$password) {
            return false;
        }

        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        return true;
    }
}
