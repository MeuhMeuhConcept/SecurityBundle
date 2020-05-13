<?php

namespace Mmc\Security\Event;

use Mmc\Security\Entity\Enum\AuthType;
use Mmc\Security\Entity\UserAuth;

class AuthenticationTokenByEmailListener
{
    public function onAuthenticationSuccess(MmcAuthenticationEvent $event)
    {
        $authEntity = $event->getAuthEntity();

        if ($authEntity->getType() != AuthType::TOKEN_BY_EMAIL) {
            return;
        }

        // Reset password
        $authEntity->setData('password', '');
    }
}
