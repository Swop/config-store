<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Manager;

use ConfigStore\Exception\AlreadyExistingConfigKeyInMultipleAppsException;
use ConfigStore\Model\AppGroup;
use Doctrine\Common\Persistence\ObjectManager;
use ConfigStore\Exception\IncompatibleAppsException;
use ConfigStore\Model\App;
use ConfigStore\Model\ConfigItem;
use ConfigStore\Repository\ConfigItemRepository;

class ConfigManager
{
    /** @var ConfigItemRepository */
    protected $configItemRepository;
    /** @var ObjectManager */
    protected $persistenceManager;
    /** @var array */
    protected $diffCache;

    /**
     * @param ConfigItemRepository $configItemRepository
     * @param ObjectManager        $persistenceManager
     */
    public function __construct(ConfigItemRepository $configItemRepository, ObjectManager $persistenceManager)
    {
        $this->configItemRepository = $configItemRepository;
        $this->persistenceManager   = $persistenceManager;
        $this->diffCache            = array();
    }

    /**
     * Gets other config items which share the same key on other apps from the same app group.
     *
     * @param string   $configKey
     * @param AppGroup $group
     *
     * @return array
     */
    public function getCompetitorConfigItems($configKey, AppGroup $group)
    {
        return $this->configItemRepository->findCorrespondingConfigItemsFromGroupApps($configKey, $group);
    }

    /**
     * @param ConfigItem $configItem
     * @param App[]      $apps
     */
    public function addConfigItemToApps(ConfigItem $configItem, array $apps)
    {
        $configItemKey   = $configItem->getKey();
        $configItemValue = $configItem->getValue();

        $appsWithSameConfigItem = array_filter($apps, function (App $item) use ($configItemKey) {
            return $item->hasConfigItem($configItemKey);
        });

        if (0 < count($appsWithSameConfigItem)) {
            throw new AlreadyExistingConfigKeyInMultipleAppsException($configItem, $appsWithSameConfigItem);
        }

        /** @var App $app */
        foreach ($apps as $app) {
            $newConfigItem = new ConfigItem();
            $newConfigItem
                ->setApp($app)
                ->setKey($configItemKey)
                ->setValue($configItemValue)
            ;

            $app->addConfigItem($newConfigItem);
        }
    }

    /**
     * Compute the config diff between two apps
     *
     * @param App $app1
     * @param App $app2
     *
     * @return array
     */
    public function diff(App $app1, App $app2)
    {
        if (!$this->canCompare($app1, $app2)) {
            throw new IncompatibleAppsException($app1, $app2);
        }

        $diffCacheKey = $this->getDiffCacheKey($app1, $app2);

        if (array_key_exists($diffCacheKey, $this->diffCache)) {
            return $this->diffCache[$diffCacheKey];
        }

        $diff = [
            'app_left'      => $app1,
            'app_right'     => $app2,
            'keys_union'    => [],
            'identical'    => [],
            'different'    => [],
            'missing_left'  => [],
            'missing_right' => [],
        ];

        $configs1 = $app1->getConfigArray();
        $keys1    = array_keys($configs1);
        sort($keys1);
        $count1   = count($keys1);

        $configs2 = $app2->getConfigArray();
        $keys2    = array_keys($configs2);
        sort($keys2);
        $count2   = count($keys2);

        $diff['keys_union'] = array_keys(array_flip(array_merge($keys1, $keys2)));
        sort($diff['keys_union']);

        $index1 = $index2 = 0;

        while (true) {
            if ($index1 >= $count1 && $index2 >= $count2) {
                break;
            }

            if ($index1 >= $count1) {
                $diff['missing_left'][] = $keys2[$index2];
                $index2 += 1;

                continue;
            }

            if ($index2 >= $count2) {
                $diff['missing_right'][] = $keys1[$index1];
                $index1 += 1;

                continue;
            }

            $key1   = $keys1[$index1];
            $key2   = $keys2[$index2];

            $comparison = strcmp($key1, $key2);

            if (0 == $comparison) {
                $value1 = $configs1[$key1];
                $value2 = $configs2[$key2];

                if ($value1 === $value2) {
                    $diff['identical'][] = $key1;
                } else {
                    $diff['different'][] = $key1;
                }

                $index1 += 1;
                $index2 += 1;
            } elseif (0 > $comparison) {
                $diff['missing_right'][] = $key1;
                $index1 += 1;
            } elseif (0 < $comparison) {
                $diff['missing_left'][] = $key2;
                $index2 += 1;
            }
        }

        $this->diffCache[$diffCacheKey] = $diff;

        return $diff;
    }

    /**
     * Checks if the two apps can be compared (i.e. are from the same group)
     *
     * @param App $app
     * @param App $app2
     *
     * @return bool
     */
    public function canCompare(App $app, App $app2)
    {
        $group1 = $app->getGroup();
        $group2  = $app2->getGroup();

        return $group1 === $group2;
    }

    /**
     * @param array $diff
     *
     * @return bool
     */
    public function isEmptyDiff(array $diff)
    {
        return 0 == count($diff['different'])
            && 0 == count($diff['missing_left'])
            && 0 == count($diff['missing_right'])
        ;
    }

    /**
     * @param array $diff
     *
     * @return bool
     */
    public function isEmptyKeyDiff(array $diff)
    {
        return 0 == count($diff['missing_left']);
    }

    /**
     * Duplicate configuration from one app to another
     *
     * @param App $from
     * @param App $to
     */
    public function duplicateConfig(App $from, App $to)
    {
        $config = $from->getConfigArray();

        $configItems = [];

        foreach ($config as $key => $value) {
            $configItem = new ConfigItem();
            $configItem
                ->setKey($key)
                ->setValue($value)
                ->setApp($to);

            $configItems[] = $configItem;
        }

        $to->setConfigItems($configItems);
    }

    /**
     * Saves a config item
     *
     * @param ConfigItem $configItem
     */
    public function save(ConfigItem $configItem)
    {
        $this->persistenceManager->persist($configItem);
        $this->persistenceManager->flush($configItem);
    }

    /**
     * Deletes a config item
     *
     * @param ConfigItem $configItem
     */
    public function delete(ConfigItem $configItem)
    {
        $this->deleteMultiple([$configItem]);
    }

    public function deleteMultiple(array $configItems)
    {
        foreach ($configItems as $configItem) {
            $this->persistenceManager->remove($configItem);
        }

        $this->persistenceManager->flush($configItems);
    }

    private function getDiffCacheKey(App $app, App $app2)
    {
        return 'diff_' . $app->getId() . '_' . $app2->getId();
    }
}
