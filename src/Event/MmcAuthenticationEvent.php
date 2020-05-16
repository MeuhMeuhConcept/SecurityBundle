<?php

namespace Mmc\Security\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MmcAuthenticationEvent extends Event
{
    private $token;

    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
}
