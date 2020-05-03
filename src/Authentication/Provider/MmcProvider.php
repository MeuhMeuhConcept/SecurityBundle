<?php

namespace Mmc\Security\Authentication\Provider;

use Mmc\Security\Authentication\Authenticator\AuthenticatorInterface;
use Mmc\Security\Authentication\Token\MmcToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MmcProvider implements AuthenticationProviderInterface
{
    protected $userProvider;
    protected $authenticators;

    public function __construct(
        UserProviderInterface $userProvider,
        iterable $authenticators
    ) {
        $this->userProvider = $userProvider;
        $this->authenticators = $authenticators;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = null;
        if ('username_password' == $token->getType()) {
            // trying to basicly get user (for incompitable user provider)
            try {
                $user = $this->userProvider->loadUserByUsername($token->getKey());
            } catch (UsernameNotFoundException $e) {
            }
        }

        if (!$user) {
            $user = $this->userProvider->loadUserByUsername($token->getType().':'.$token->getKey());
        }

        if ($user && $this->validate($token, $user)) {
            $authenticatedToken = new MmcToken($token->getType(), $token->getKey(), $token->getProviderKey(), ['IS_AUTHENTICATED_FULLY']);
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The MMC authentication failed.');
    }

    protected function validate(MmcToken $token, UserInterface $user)
    {
        foreach ($this->authenticators as $key => $authenticator) {
            if ($authenticator instanceof AuthenticatorInterface && $authenticator->supports($token, $user)) {
                return $authenticator->authenticate($token, $user);
            }
        }

        return false;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof MmcToken;
    }
}
