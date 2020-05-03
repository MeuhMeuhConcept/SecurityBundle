<?php

namespace Mmc\Security\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`",indexes={@ORM\Index(name="uuid_idx", columns={"uuid"})})
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="guid")
     */
    protected $uuid;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isEnabled;

    /**
     * @ORM\OneToMany(targetEntity="UserAuth", mappedBy="user")
     */
    protected $auths;

    public function __construct()
    {
        $this->uuid = uuid_create(UUID_TYPE_RANDOM);
        $this->isEnabled = true;
        $this->auths = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $isEnabled
     *
     * @return self
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }
}
