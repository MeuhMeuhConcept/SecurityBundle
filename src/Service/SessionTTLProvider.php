<?php

namespace Mmc\Security\Service;

class SessionTTLProvider
{
    protected $config;

    public function __construct(
        $config
    ) {
        $this->config = $config;
    }

    public function getSessionTTL(string $type): int
    {
        if (isset($this->config[$type])) {
            return intval($this->config[$type]);
        }

        return intval($this->config['default']);
    }
}
