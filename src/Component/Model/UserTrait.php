<?php

namespace Mmc\Security\Component\Model;

trait UserTrait
{
    protected $uuid;

    protected $isEnabled;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->uuid;
    }

    public function eraseCredentials()
    {
    }
}
