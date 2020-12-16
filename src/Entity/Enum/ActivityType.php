<?php

namespace Mmc\Security\Entity\Enum;

use Greg0ire\Enum\AbstractEnum;

final class ActivityType extends AbstractEnum
{
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const CHANGE_PASSWORD = 'change_password';
    const CHANGE_USERNAME = 'change_username';
    const REFRESH_TOKEN_BY_EMAIL = 'refresh_token_email';
    const TOKEN_BY_EMAIL_HAS_BEEN_VERIFIED = 'token_email_verified';
    const CREATE_EMAIL = 'create_email';
    const CHANGE_EMAIL = 'change_email';
}
