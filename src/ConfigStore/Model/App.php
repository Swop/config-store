<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ConfigStore\Exception\AlreadyExistingConfigKeyException;
use ConfigStore\Exception\UnknownConfigKeyException;

/**
 * Registered application, which can own several config items values.
 */
class App
{
    /** @var int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var string $slug */
    private $slug;
    /** @var string $description */
    private $description;
    /** @var AppGroup $group */
    private $group;
    /** @var string $accessKey */
    private $accessKey;
    /** @var ConfigItem[] $configItems */
    private $configItems;
    /** @var \DateTime */
    private $createdAt;
    /** @var \DateTime */
    private $updatedAt;

    public function __construct()
    {
        $this->configItems = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @return Collection
     */
    public function getConfigItems()
    {
        return $this->configItems;
    }

    public function setConfigItems($configItems)
    {
        $this->configItems = new ArrayCollection();

        if ($configItems instanceof Collection) {
            $configItems = $configItems->toArray();
        }

        foreach ($configItems as $configItem) {
            $this->addConfigItem($configItem);
        }
    }

    public function removeConfigItem($configItem)
    {
        $this->configItems->removeElement($configItem);
    }

    public function removeConfigItem2($configKey)
    {
        $deleteIndex = null;

        foreach ($this->configItems as $key => $value) {
            if ($value->getKey() === $configKey) {
                $deleteIndex = $key;
                break;
            }
        }

        if (null !== $deleteIndex) {
            $this->configItems->remove($deleteIndex);
        }
    }

    /**
     * Adds a config item in the app config set
     *
     * @param ConfigItem $configItem
     *
     * @throws \ConfigStore\Exception\AlreadyExistingConfigKeyException
     */
    public function addConfigItem(ConfigItem $configItem)
    {
        if ($this->hasConfigItem($configItem)) {
            throw new AlreadyExistingConfigKeyException($configItem, $this);
        }

        $configItem->setApp($this);

        $this->configItems[] = $configItem;
    }

    /**
     * Checks if the current app already owns the given config item
     *
     * @param ConfigItem|string $configKey Config item or its key
     *
     * @return bool
     */
    public function hasConfigItem($configKey)
    {
        if ($configKey instanceof ConfigItem) {
            $configKey = $configKey->getKey();
        }

        return 0 < count($this->filterConfigItems($configKey));
    }

    public function getConfigItem($configKey)
    {
        $configItems = $this->filterConfigItems($configKey);

        if (0 === count($configItems)) {
            throw new UnknownConfigKeyException($configKey, $this);
        }

        return array_shift($configItems);
    }

    /**
     * Filter the config items by config key
     *
     * @param string $configKey
     *
     * @return array
     */
    protected function filterConfigItems($configKey)
    {
        return array_filter(
            $this->configItems->toArray(),
            function ($item) use ($configKey) {
                /** @var ConfigItem $item */
                if ($item->getKey() === $configKey) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Get all the config keys
     *
     * @return array
     */
    public function getConfigKeys()
    {
        return array_reduce(
            $this->configItems->toArray(),
            function ($keys, $item) {
                /** @var ConfigItem $item */
                $keys[] = $item->getKey();

                return $keys;
            },
            []
        );
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \ConfigStore\Model\AppGroup|null
     */
    public function getGroup()
    {
        return $this->group;
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
     * Gets the slug attribute
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the description attribute
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the group attribute
     *
     * @param \ConfigStore\Model\AppGroup $group
     *
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
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
            throw new \LogicException('The application name must be a non-empty string');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @param string $accessKey
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function setAccessKey($accessKey)
    {
        if (!is_string($accessKey) || empty($accessKey)) {
            throw new \LogicException('The application access key must be a non-empty string');
        }

        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return array_reduce(
            $this->getConfigItems()->toArray(),
            function ($configArray, $item) {
                /** @var ConfigItem $item */
                $configArray[$item->getKey()] = $item->getValue();

                return $configArray;
            },
            []
        );
    }

    /**
     * Gets the createdAt attribute
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets the updatedAt attribute
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return bool
     */
    public function isRef()
    {
        return $this->group && $this === $this->group->getReference();
    }

    /**
     * Get app reference
     *
     * @return null|App
     */
    public function getRef()
    {
        if (null !== $group = $this->getGroup()) {
            return $group->getReference();
        }

        return null;
    }

    /**
     * Get app string representation (for security usage)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSlug();
    }
}
