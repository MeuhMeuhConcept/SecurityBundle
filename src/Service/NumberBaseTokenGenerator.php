<?php

namespace Mmc\Security\Service;

class NumberBaseTokenGenerator implements TokenGenerator
{
    protected $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    public function generate(): string
    {
        $max = pow(10, $this->size) - 1;

        return str_pad(rand(0, $max), $this->size, '0');
    }
}
