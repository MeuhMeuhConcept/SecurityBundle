<?php

namespace Mmc\Security\User;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    public function getUuid();

    public function getUserUuid();

    public function getType();

    public function getKey();

    public function getIsVerified();

    public function getDatas();

    public function getData($key);
}
