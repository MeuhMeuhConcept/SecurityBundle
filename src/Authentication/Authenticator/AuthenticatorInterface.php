<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token \MmcToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthenticatorInterface
{
    public function supports(MmcToken $token, UserInterface $user): bool;

    public function authenticate(MmcToken $token, UserInterface $user): bool;
}
