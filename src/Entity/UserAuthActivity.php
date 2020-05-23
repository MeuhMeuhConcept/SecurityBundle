<?php

namespace Mmc\Security\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Greg0ire\Enum\Bridge\Symfony\Validator\Constraint\Enum as EnumAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_auth_activity")
 */
class UserAuthActivity
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserAuth", inversedBy="activities")
     */
    protected $userAuth;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    protected $sessionUuid;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @EnumAssert("Mmc\Security\Entity\Enum\ActivityType")
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\Column(type="json")
     */
    protected $datas;

    public function __construct()
    {
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
    public function getSessionUuid()
    {
        return $this->sessionUuid;
    }

    /**
     * @param mixed $sessionUuid
     *
     * @return self
     */
    public function setSessionUuid($sessionUuid)
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }
}
