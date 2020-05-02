<?php

namespace Mmc\Security\Component\Model;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface
{
    public function getUuid(): string;

    public function isEnabled(): bool;
}
