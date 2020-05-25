<?php

namespace Mmc\Security\Entity\Enum;

use Greg0ire\Enum\AbstractEnum;

final class ActivityType extends AbstractEnum
{
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const CHANGE_PASSWORD = 'change_password';
}
