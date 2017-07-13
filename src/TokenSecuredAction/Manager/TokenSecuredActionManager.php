<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace TokenSecuredAction\Manager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class TokenSecuredActionManager
 */
class TokenSecuredActionManager
{
    /** QueryString parameter used to embed CSRF token */
    const URL_TOKEN_REQUEST_KEY = '_token';

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->urlGenerator     = $urlGenerator;
    }

    /**
     * @param string $routeName  Route name
     * @param string $intention  Token intention
     * @param array  $parameters Route parameter
     * @param bool   $relative   Relative url
     *
     * @return string
     */
    public function generateSecuredActionUrl(
        $routeName,
        $intention = 'default',
        $parameters = array(),
        $relative = false
    ) {
        $parameters[self::URL_TOKEN_REQUEST_KEY] = $this->generateToken($intention);

        return $this->urlGenerator->generate(
            $routeName,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * @param string $intention
     *
     * @return string
     */
    public function generateToken($intention = 'default')
    {
        return $this->csrfTokenManager->getToken($intention)->getValue();
    }

    /**
     * @param string  $intention
     * @param Request $request
     *
     * @return bool
     */
    public function isLegitimateRequest($intention, Request $request)
    {
        $providedToken = new CsrfToken($intention, $request->get(self::URL_TOKEN_REQUEST_KEY));

        return $this->csrfTokenManager->isTokenValid($providedToken);
    }

    /**
     * @return string
     */
    public function getUrlTokenRequestKey()
    {
        return self::URL_TOKEN_REQUEST_KEY;
    }
}
