<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Entity\Enum\AuthType;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenByEmailAuthenticator extends AbstractTokenAuthenticator
{
    public function supports(MmcToken $token, UserInterface $user): bool
    {
        return AuthType::TOKEN_BY_EMAIL == $token->getType();
    }
}
