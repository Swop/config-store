<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Security\User;

use ConfigStore\Exception\UnknownAppException;
use ConfigStore\Manager\AppManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyBasedAppProvider implements UserProviderInterface
{
    /** @var AppManager $appManager */
    private $appManager;

    /**
     * @param AppManager $appManager
     */
    public function __construct(AppManager $appManager)
    {
        $this->appManager = $appManager;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $apiKey = $username;

        try {
            $app = $this->appManager->getByAccessKey($apiKey);
        } catch (UnknownAppException $e) {
            throw new UsernameNotFoundException();
        }

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        // We use stateless authentication here...
        throw new UnsupportedUserException();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $supportedClass = '\ConfigStore\Model\App';
        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }
}
