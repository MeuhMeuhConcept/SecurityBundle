<?php

namespace Mmc\Security\Event;

use Mmc\Security\Entity\Enum\AuthType;

class AuthenticationTokenByEmailListener
{
    public function onAuthenticationSuccess(MmcAuthenticationEvent $event)
    {
        $authEntity = $event->getAuthEntity();

        if (AuthType::TOKEN_BY_EMAIL != $authEntity->getType()) {
            return;
        }

        // Reset password
        $authEntity->setData('password', '');
    }
}
