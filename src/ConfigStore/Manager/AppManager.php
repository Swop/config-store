<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use ConfigStore\Exception\UnknownAppException;
use ConfigStore\Exception\UnknownGroupException;
use ConfigStore\Model\App;
use ConfigStore\Model\AppGroup;
use ConfigStore\Repository\AppRepository;
use ConfigStore\ApiKey\ApiKeyGenerator;

class AppManager
{
    /** @var AppRepository */
    protected $appRepository;
    /** @var ObjectRepository */
    protected $groupRepository;
    /** @var ObjectManager */
    protected $persistenceManager;
    /** @var ConfigManager */
    private $configManager;
    /** @var ApiKeyGenerator */
    private $apiKeyGenerator;

    /**
     * @param AppRepository                                 $appRepository
     * @param \Doctrine\Common\Persistence\ObjectRepository $groupRepository
     * @param \Doctrine\Common\Persistence\ObjectManager    $persistenceManager
     * @param ConfigManager                                 $configManager
     */
    public function __construct(
        AppRepository $appRepository,
        ObjectRepository $groupRepository,
        ObjectManager $persistenceManager,
        ConfigManager $configManager
    ) {
        $this->appRepository      = $appRepository;
        $this->groupRepository    = $groupRepository;
        $this->persistenceManager = $persistenceManager;
        $this->configManager      = $configManager;
    }

    /**
     * @param ApiKeyGenerator $apiKeyGenerator
     *
     * @return $this
     */
    public function setApiKeyGenerator($apiKeyGenerator)
    {
        $this->apiKeyGenerator = $apiKeyGenerator;

        return $this;
    }

    /**
     * Find an app which have the given slug
     *
     * @param string $appSlug
     *
     * @return App
     *
     * @throws \ConfigStore\Exception\UnknownAppException
     */
    public function getBySlug($appSlug)
    {
        if (null === $app = $this->appRepository->findBySlug($appSlug)) {
            throw new UnknownAppException($appSlug);
        }

        return $app;
    }

    /**
     * Get all apps
     *
     * @return array
     */
    public function all()
    {
        return $this->appRepository->findAllApps();
    }

    public function getStandaloneApps()
    {
        return $this->appRepository->findStandaloneApps();
    }

    /**
     * Find a group which have the given id
     *
     * @param int $groupId
     *
     * @return AppGroup
     *
     * @throws \ConfigStore\Exception\UnknownGroupException
     */
    public function getGroup($groupId)
    {
        if (null === $group = $this->groupRepository->find($groupId)) {
            throw new UnknownGroupException($groupId);
        }

        return $group;
    }

    /**
     * Get all groups
     *
     * @return AppGroup[]
     */
    public function allGroups()
    {
        return $this->groupRepository->findAll();
    }

    /**
     * @param App $app
     */
    public function revokeAccessKey(App $app)
    {
        $app->setAccessKey($this->apiKeyGenerator->generate());

        $this->saveApp($app);
    }

    /**
     * Find an app which have the given access key
     *
     * @param string $accessKey
     *
     * @return App
     *
     * @throws \ConfigStore\Exception\UnknownAppException
     *
     */
    public function getByAccessKey($accessKey)
    {
        if (null === $app = $this->appRepository->findByAccessKey($accessKey)) {
            throw new UnknownAppException($accessKey);
        }

        return $app;
    }

    /**
     * Checks if the given access key is attached to an App
     *
     * @param string $accessKey
     *
     * @return bool
     */
    public function isValidAccessKey($accessKey)
    {
        return $this->appRepository->isValidAccessKey($accessKey);
    }

    /**
     * @param App $app
     *
     * @return null|App
     */
    public function getReferenceApp(App $app)
    {
        return $app->getRef();
    }

    /**
     * @param App $app
     *
     * @return array|null
     */
    public function getDiffWithReference(App $app)
    {
        $ref = $this->getReferenceApp($app);

        if (null === $ref) {
            return null;
        }

        return $this->getDiff($app, $ref);
    }

    /**
     * Checks if the given app is out of sync (difference in config keys) with tis reference, if it has one.
     *
     * @param App $app
     *
     * @return bool
     */
    public function isOutOfSyncWithReference(App $app)
    {
        $diff = $this->getDiffWithReference($app);

        if (is_array($diff)) {
            return !$this->configManager->isEmptyKeyDiff($diff);
        } else {
            return false;
        }
    }

    /**
     * @param App $app
     * @param App $app2
     *
     * @return array
     */
    public function getDiff(App $app, App $app2)
    {
        return $this->configManager->diff($app, $app2);
    }

    /**
     * Save an app
     *
     * @param App $app
     */
    public function saveApp(App $app)
    {
        $this->saveObject($app);
    }

    /**
     * @param App[] $apps
     */
    public function saveMultipleApps($apps)
    {
        $this->saveObjects($apps);
    }

    /**
     * Delete an app
     *
     * @param App $app
     */
    public function deleteApp(App $app)
    {
        $this->deleteObject($app);
    }

    /**
     * @param App[] $apps
     */
    public function deleteMultipleApps($apps)
    {
        $this->deleteObjects($apps);
    }

    /**
     * Save an app group
     *
     * @param AppGroup $group
     */
    public function saveGroup(AppGroup $group)
    {
        $this->saveObject($group);
    }

    /**
     * Delete an app group
     *
     * @param AppGroup $group
     */
    public function deleteGroup(AppGroup $group)
    {
        $this->deleteObject($group);
    }

    /**
     * Save an app or a group
     *
     * @param App|AppGroup $object
     */
    protected function saveObject($object)
    {
        $this->persistenceManager->persist($object);
        $this->persistenceManager->flush();
    }

    /**
     * Save several apps or groups
     *
     * @param App[]|AppGroup[] $objects
     */
    protected function saveObjects($objects)
    {
        foreach ($objects as $object) {
            $this->persistenceManager->persist($object);
        }

        $this->persistenceManager->flush();
    }

    /**
     * Delete an app or a group
     *
     * @param App|AppGroup $object
     */
    protected function deleteObject($object)
    {
        $this->persistenceManager->remove($object);
        $this->persistenceManager->flush($object);
    }

    /**
     * Delete multiple apps or groups
     *
     * @param App[]|AppGroup[] $objects
     */
    protected function deleteObjects($objects)
    {
        foreach ($objects as $object) {
            $this->persistenceManager->remove($object);
        }

        $this->persistenceManager->flush();
    }
}
