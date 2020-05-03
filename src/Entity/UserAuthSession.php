<?php

namespace Mmc\Security\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_auth_session", indexes={@ORM\Index(name="user_auth_session_uuid_idx", columns={"uuid"})})
 */
class UserAuthSession
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserAuth", inversedBy="sessions")
     */
    protected $userAuth;

    /**
     * @ORM\Column(type="guid")
     */
    protected $uuid;

    /**
     * @ORM\Column(type="json")
     */
    protected $datas;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
        $this->datas = [];
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
    public function getUserAuth()
    {
        return $this->userAuth;
    }

    /**
     * @param mixed $userAuth
     *
     * @return self
     */
    public function setUserAuth($userAuth)
    {
        $this->userAuth = $userAuth;

        return $this;
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
}
