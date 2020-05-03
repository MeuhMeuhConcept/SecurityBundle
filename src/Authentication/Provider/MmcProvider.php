<?php

namespace Mmc\Security\Authentication\Provider;

use Mmc\Security\Authentication\Authenticator\AuthenticatorInterface;
use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
        $user = $this->userProvider->loadUserByTypeAndKey($token->getType(), $token->getKey());

        if ($user && $this->validate($token, $user)) {
            $authenticatedToken = new MmcToken($token->getType(), $token->getKey(), $token->getProviderKey(), ['IS_AUTHENTICATED_FULLY']);
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The MMC authentication failed.');
    }

    protected function validate(MmcToken $token, User $user)
    {
        foreach ($this->authenticators as $key => $authenticator) {
            if ($authenticator instanceof AuthenticatorInterface && $authenticator->supports($token)) {
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
