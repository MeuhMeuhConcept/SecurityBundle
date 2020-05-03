<?php

namespace Mmc\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class MmcToken extends AbstractToken
{
    protected $type;
    protected $key;
    protected $providerKey;
    protected $extras;

    public function __construct(string $type, string $key, string $providerKey, array $roles = [])
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->type = $type;
        $this->key = $key;
        $this->providerKey = $providerKey;
        $this->extras = [];

        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * @return mixed
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param mixed $extra
     *
     * @return self
     */
    public function setExtras(array $extras = [])
    {
        $this->extras = $extras;

        return $this;
    }

    public function getExtra($key)
    {
        if (isset($this->extras[$key])) {
            return $this->extras[$key];
        }

        return null;
    }
}
