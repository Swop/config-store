<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AppGroup
{
    /** @var int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var Collection */
    private $apps;
    /** @var App */
    private $reference;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apps = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getApps()
    {
        return $this->apps;
    }

    /**
     * @param App $app
     */
    public function addApp(App $app)
    {
        if (!$this->apps->contains($app)) {
            $this->apps->add($app);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function setName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new \LogicException('The group name must be a non-empty string');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @param App $app
     */
    public function setReference(App $app = null)
    {
        if ($app !== null && $app->getGroup() !== $this) {
            throw new \DomainException('An application must be part of the group if you want to use it as a reference for the group');
        }

        $this->reference = $app;
    }

    /**
     * Gets the reference attribute
     *
     * @return App
     */
    public function getReference()
    {
        return $this->reference;
    }
}
