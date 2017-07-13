<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Model;

/**
 * Configuration key/value item
 */
class ConfigItem
{
    /** @var App $app */
    private $app;
    /** @var string $key */
    private $key;
    /** @var string $value */
    private $value;

    /**
     * @return \ConfigStore\Model\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param \ConfigStore\Model\App $app
     *
     * @return $this
     */
    public function setApp(App $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Sets the key attribute
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Sets the value attribute
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
