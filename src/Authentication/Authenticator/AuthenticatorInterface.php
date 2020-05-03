<?php

namespace Mmc\Security\Authentication\Authenticator;

use Mmc\Security\Authentication\Token \MmcToken;
use Mmc\Security\User\User;

interface AuthenticatorInterface
{
    public function supports(MmcToken $token): bool;

    public function authenticate(MmcToken $token, User $user): bool;
}
