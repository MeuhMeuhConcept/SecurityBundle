<?php

namespace Mmc\Security\Event;

use Mmc\Security\Entity\UserAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MmcAuthenticationRelativeUserAuthEvent extends MmcAuthenticationEvent
{
    private $authEntity;
    private $request;

    public function __construct(TokenInterface $token, UserAuth $authEntity, Request $request = null)
    {
        parent::__construct($token);
        $this->authEntity = $authEntity;
        $this->request = $request;
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
