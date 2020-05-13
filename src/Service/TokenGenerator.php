<?php

namespace Mmc\Security\Service;

interface TokenGenerator
{
    public function generate(): string;
}
