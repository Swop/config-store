<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Event;

use ConfigStore\ApiKey\ApiKeyGenerator;
use ConfigStore\Model\App;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppPersistanceSubscriber
 *
 * @package \ConfigStore\Event
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class AppPersistenceSubscriber implements EventSubscriber
{
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $apiKeyGeneratorServiceName;

    /**
     * @param ContainerInterface $container
     * @param string             $apiKeyGeneratorServiceName
     */
    public function __construct(ContainerInterface $container, $apiKeyGeneratorServiceName)
    {
        $this->container                  = $container;
        $this->apiKeyGeneratorServiceName = $apiKeyGeneratorServiceName;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof App) {
            $new = null === $entity->getId();

            // If the app is a new instance, a new Api Key is assigned to it automatically
            $accessKey = $entity->getAccessKey();
            if ($new && empty($accessKey)) {
                $entity->setAccessKey($this->container->get($this->apiKeyGeneratorServiceName)->generate());
            }
        }
    }
}
