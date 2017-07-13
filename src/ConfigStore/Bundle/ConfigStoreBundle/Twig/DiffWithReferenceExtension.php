<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Twig;

use ConfigStore\Manager\AppManager;
use ConfigStore\Model\App;

class DiffWithReferenceExtension extends \Twig_Extension
{
    /** @var AppManager $appManager */
    private $appManager;

    /**
     * @param AppManager $appManager
     */
    public function __construct(AppManager $appManager)
    {
        $this->appManager    = $appManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('diff_with_ref', array($this, 'getDiffWithReference')),
            new \Twig_SimpleFunction('is_out_of_sync', array($this, 'isOutOfSyncWithRef')),
        );
    }

    /**
     * @param App $app
     *
     * @return array|null
     */
    public function getDiffWithReference(App $app)
    {
        return $this->appManager->getDiffWithReference($app);
    }

    /**
     * Returns if the
     * @param App $app
     *
     * @return bool
     */
    public function isOutOfSyncWithRef(App $app)
    {
        return $this->appManager->isOutOfSyncWithReference($app);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'config_store_twig_diff_with_reference';
    }
}
