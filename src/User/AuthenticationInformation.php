<?php

namespace Mmc\Security\User;

class AuthenticationInformation
{
    protected $type;
    protected $key;
    protected $isVerified;
    protected $datas;

    public function __construct(
        $type,
        $key,
        $isVerified,
        $datas
    ) {
        $this->type = $type;
        $this->key = $key;
        $this->isVerified = $isVerified;
        $this->datas = $datas;
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

    public function eraseCredentials()
    {
        $this->datas = [];
    }
}
