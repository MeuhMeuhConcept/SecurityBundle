<?php

namespace Mmc\Security\Event;

use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Entity\UserAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class MmcAuthenticationEvent extends Event
{
    private $token;
    private $authEntity;
    private $request;

    public function __construct(MmcToken $token, UserAuth $authEntity, Request $request)
    {
        $this->token = $token;
        $this->authEntity = $authEntity;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getAuthEntity()
    {
        return $this->authEntity;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }
}
