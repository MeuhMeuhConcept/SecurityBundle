<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Entity\Enum\AuthType;
use Symfony\Component\Security\Core\User\UserInterface;

class AnonymousAuthenticator implements AuthenticatorInterface
{
    public function supports(MmcToken $token, UserInterface $user): bool
    {
        return AuthType::ANONYMOUS == $token->getType();
    }

    public function authenticate(MmcToken $token, UserInterface $user): bool
    {
        return true;
    }
}
