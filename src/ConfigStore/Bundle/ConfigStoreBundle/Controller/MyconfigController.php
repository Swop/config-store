<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use ConfigStore\Model\App;
use ConfigStore\Model\ConfigItem;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class MyconfigController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Gets all the authenticated application config items
     *
     * @return App
     *
     * @Rest\View(serializerGroups="App")
     */
    public function getAction()
    {
        $app        = $this->get('security.context')->getToken()->getUser();
        $appManager = $appManager = $this->get('config_store.manager.app');

        // Check coherence with the reference app
        if (null !== $referenceApp = $appManager->getReferenceApp($app)) {
            $configManager = $this->get('config_store.manager.config');
            $diff = $configManager->diff($app, $referenceApp);

            if (!$configManager->isEmptyKeyDiff($diff)) {
                throw new ConflictHttpException(
                    'The configuration doesn\'t match with the reference. Please update the config before using it in your program.'
                );
            }
        }

        return $app;
    }
}
