<?php

namespace Mmc\Security\User;

class User implements UserInterface
{
    protected $uuid;

    protected $sessionUuid;

    protected $userUuid;

    protected $type;

    protected $key;

    protected $isVerified;

    protected $datas;


    public function __construct(
        $uuid,
        $sessionUuid,
        $userUuid,
        $type,
        $key,
        $isVerified,
        $datas
    ) {
        $this->uuid = $uuid;
        $this->sessionUuid = $sessionUuid;
        $this->userUuid = $userUuid;
        $this->type = $type;
        $this->key = $key;
        $this->isVerified = $isVerified;
        $this->datas = $datas;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return $this->getData('password');
    }

    public function getSalt()
    {
        return $this->getData('salt');
    }

    public function getUsername()
    {
        return $this->sessionUuid;
    }

    public function eraseCredentials()
    {
        $this->type = '';
        $this->key = '';
        $this->datas = [];
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return mixed
     */
    public function getUserUuid()
    {
        return $this->userUuid;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * @return mixed
     */
    public function getDatas()
    {
        return $this->datas;
    }

    public function getData($key)
    {
        if (isset($this->datas[$key])) {
            return $this->datas[$key];
        }

        return null;
    }
}
