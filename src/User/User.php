<?php

namespace Mmc\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    protected $uuid;

    protected $auth;

    public function __construct($uuid, AuthenticationInformation $auth = null)
    {
        $this->uuid = $uuid;
        $this->auth = $auth;
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
        if ($this->auth) {
            $this->auth->eraseCredentials();
        }
    }

    public function getAuthenticationInformation()
    {
        return $this->auth;
    }
}
