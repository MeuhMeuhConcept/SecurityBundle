<?php

namespace Mmc\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenExpiredException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Token has expired.';
    }
}
