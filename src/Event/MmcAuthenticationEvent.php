<?php

namespace Mmc\Security\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MmcAuthenticationEvent extends Event
{
    private $token;
    private $extras;

    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
        $this->extras = [];
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    public function getExtra($name)
    {
        return isset($this->extras[$name]) ? $this->extras[$name] : null;
    }

    public function setExtra($name, $value)
    {
        $this->extras[$name] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param mixed $extras
     *
     * @return self
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;

        return $this;
    }
}
