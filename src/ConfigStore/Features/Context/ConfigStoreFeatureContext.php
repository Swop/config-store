<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use ConfigStore\Model\App;
use ConfigStore\Model\AppGroup;
use ConfigStore\Model\ConfigItem;

abstract class ConfigStoreFeatureContext implements Context, SnippetAcceptingContext
{
    use DatabaseContextTrait;

    /**
     * @BeforeScenario
     */
    public function setupScenario($event)
    {
        $this->resetDatabase();
    }

    /**
     * @Given the following groups:
     */
    public function setupGroups(TableNode $groupTable)
    {
        $appManager = $this->getAppManager();

        foreach ($groupTable->getHash() as $groupHash) {
            if (isset($groupHash['name'])) {
                $groupName = $groupHash['name'];
            } else {
                throw new \LogicException('You must declare a group name');
            }

            $group = new AppGroup();
            $group->setName($groupName);

            $appManager->saveGroup($group);
        }
    }

    /**
     * @Given the following apps:
     */
    public function setupApps(TableNode $appTable)
    {
        $appManager = $this->getAppManager();

        foreach ($appTable->getHash() as $appHash) {
            if (isset($appHash['name'])) {
                $appName = $appHash['name'];
            } else {
                throw new \LogicException('You must declare an app name');
            }

            $apiKey = isset($appHash['api_key']) ? $appHash['api_key'] : uniqid(time());

            $app = new App();
            $app->setName($appName);
            $app->setAccessKey($apiKey);

            if (isset($appHash['description']) && 'null' !== $appHash['description']) {
                $app->setDescription($appHash['description']);
            }

            $groupId = null;
            $group   = null;
            if (isset($appHash['group_id']) && 'null' !== $appHash['group_id']) {
                $groupId = (int)$appHash['group_id'];
                $group   = $appManager->getGroup($groupId);

                $app->setGroup($group);
            }

            if (isset($appHash['reference']) && null !== $group && $appHash['reference'] === 'true') {
                $group->setReference($app);
            }

            $appManager->saveApp($app);

            if (null !== $group) {
                $appManager->saveGroup($group);
            }

            if (isset($appHash['config_items'])) {
                if ($configItemsHash = @json_decode($appHash['config_items'], true)) {
                    foreach ($configItemsHash as $key => $value) {
                        $config = new ConfigItem();
                        $config
                            ->setApp($app)
                            ->setKey($key)
                            ->setValue($value)
                        ;

                        $app->addConfigItem($config);
                    }
                }
            }

            $appManager->saveApp($app);
        }
    }

    /**
     * @Given /^I set the config (.*) to (.*) for the app (.*)$/
     */
    public function iSetTheConfigToForTheApp($configKey, $configValue, $appSlug)
    {
        $appManager = $this->getAppManager();

        $app = $appManager->getBySlug($appSlug);

        $config = new ConfigItem();
        $config
            ->setApp($app)
            ->setKey($configKey)
            ->setValue($configValue)
        ;

        $app->addConfigItem($config);

        $appManager->saveApp($app);
    }

    /**
     * {@inheritDoc}
     */
    protected function isConnectionReadOnly($connexionName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function isTableReadOnly($connexionName, $tableName)
    {
        return false;
    }

    /**
     * @return \ConfigStore\Manager\AppManager
     */
    abstract protected function getAppManager();
}
