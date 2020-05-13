<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Exception\TokenExpiredException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractTokenAuthenticator implements AuthenticatorInterface
{
    protected $encoderFactory;

    public function __construct(
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->encoderFactory = $encoderFactory;
    }

    abstract public function supports(MmcToken $token, UserInterface $user): bool;

    public function authenticate(MmcToken $token, UserInterface $user): bool
    {
        if (!$user || !$user->getPassword() || !$user->getData('expired_at')) {
            return false;
        }

        $expiredAt = new \Datetime($user->getData('expired_at'));
        $now = new \Datetime();

        if ($now > $expiredAt) {
            throw new TokenExpiredException();
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
