<?php

namespace Mmc\Security\Entity\Enum;

use Greg0ire\Enum\AbstractEnum;

final class AuthType extends AbstractEnum
{
    const USERNAME_PASSWORD = 'username_password';
    const TOKEN_BY_EMAIL = 'token_by_email';
    const ANONYMOUS = 'anonymous';
}
