<?php

namespace Mmc\Security\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_auth",indexes={@ORM\Index(name="search_idx", columns={"type", "key"})})
 * @UniqueEntity(fields={"type", "key"})
 */
class UserAuth
{
    use TimestampableEntity;

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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="auths")
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isEnabled;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $key;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isVerified;

    /**
     * @ORM\Column(type="json")
     */
    protected $datas;

    /**
     * @ORM\OneToMany(targetEntity="UserAuthActivity", mappedBy="userAuth")
     */
    protected $activities;

    /**
     * @ORM\OneToMany(targetEntity="UserAuthSession", mappedBy="userAuth")
     */
    protected $sessions;

    public function __construct()
    {
        $this->uuid = uuid_create(UUID_TYPE_RANDOM);
        $this->datas = [];
        $this->isEnabled = true;
        $this->isVerified = false;
        $this->activities = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
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

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     *
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * @param mixed $isVerified
     *
     * @return self
     */
    public function setIsVerified($isVerified)
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @param mixed $datas
     *
     * @return self
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    public function getData($name)
    {
        return isset($this->datas[$name]) ? $this->datas[$name] : null;
    }

    public function setData($name, $value)
    {
        $this->datas[$name] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @return mixed
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}
