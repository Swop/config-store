<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace TokenSecuredAction\Twig;

use TokenSecuredAction\Manager\TokenSecuredActionManager;

/**
 * Class SecureLinkExtension
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Twig
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class SecureLinkExtension extends \Twig_Extension
{
    /** @var TokenSecuredActionManager */
    private $tokenSecuredActionManager;

    /**
     * @param TokenSecuredActionManager $tokenSecuredActionManager
     */
    public function __construct(TokenSecuredActionManager $tokenSecuredActionManager)
    {
        $this->tokenSecuredActionManager = $tokenSecuredActionManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('secure_path', array($this, 'getSecuredPath')),
        );
    }

    /**
     * @param string $name       Route name
     * @param string $intention  Token intention
     * @param array  $parameters Route parameter
     * @param bool   $relative   Relative url
     *
     * @return string
     */
    public function getSecuredPath($name, $intention = 'default', $parameters = array(), $relative = false)
    {
        return $this->tokenSecuredActionManager->generateSecuredActionUrl($name, $intention, $parameters, $relative);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'config_store_twig_secure_link';
    }
}
