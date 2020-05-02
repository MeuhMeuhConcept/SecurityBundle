<?php

namespace Mmc\Security\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mmc\Security\Component\Model\UserTrait as BaseUserTrait;

trait UserTrait
{
    use BaseUserTrait;

    /**
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $uuid;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isEnabled;
}
