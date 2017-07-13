<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace TokenSecuredAction\Aop\Interceptor;

use TokenSecuredAction\Annotation\TokenSecured;
use TokenSecuredAction\Manager\TokenSecuredActionManager;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use CG\Proxy\MethodInvocation;
use CG\Proxy\MethodInterceptorInterface;

/**
 * Class TokenSecuredInterceptor
 *
 * @package \TokenSecuredAction\Aop\Interceptor
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class TokenSecuredInterceptor implements MethodInterceptorInterface
{
    /** @var Reader */
    private $reader;
    /** @var TokenSecuredActionManager */
    private $tokenSecuredActionManager;
    /** @var RequestStack */
    private $requestStack;

    /**
     * @param Reader                    $reader
     * @param TokenSecuredActionManager $tokenSecuredActionManager
     * @param RequestStack              $requestStack
     */
    public function __construct(
        Reader $reader,
        TokenSecuredActionManager $tokenSecuredActionManager,
        RequestStack $requestStack
    ) {
        $this->reader                    = $reader;
        $this->tokenSecuredActionManager = $tokenSecuredActionManager;
        $this->requestStack              = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function intercept(MethodInvocation $invocation)
    {
        $request = $this->requestStack->getMasterRequest();

        if (null !== $request) {
            $reflexion = $invocation->reflection;

            /** @var TokenSecured $annotation */
            $annotation = $this->reader->getMethodAnnotation(
                $reflexion,
                '\\TokenSecuredAction\Annotation\TokenSecured'
            );

            if (!$this->tokenSecuredActionManager->isLegitimateRequest($annotation->getIntention(), $request)) {
                throw new AccessDeniedHttpException('Invalid CSRF token');
            }
        }

        return $invocation->proceed();
    }
}
