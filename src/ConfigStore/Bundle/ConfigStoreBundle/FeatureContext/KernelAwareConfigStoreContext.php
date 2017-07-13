<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\FeatureContext;

use Behat\Symfony2Extension\Context\KernelDictionary;
use ConfigStore\Features\Context\ConfigStoreFeatureContext as BaseConfigStoreContext;

class KernelAwareConfigStoreContext extends BaseConfigStoreContext
{
    use KernelDictionary;

    /**
     * {@inheritDoc}
     */
    protected function getAppManager()
    {
        return $this->getContainer()->get('config_store.manager.app');
    }

    /**
     * {@inheritDoc}
     */
    protected function getObjectManager($managerName = null)
    {
        return $this->getContainer()->get('doctrine')->getManager($managerName);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDatabaseConnection($name)
    {
        return $this->getContainer()->get('doctrine')->getConnection($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDatabaseConnections()
    {
        return $this->getContainer()->get('doctrine')->getConnections();
    }
}
