<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace TokenSecuredAction\Aop\Pointcut;

use Doctrine\Common\Annotations\Reader;
use JMS\AopBundle\Aop\PointcutInterface;

/**
 * Class TokenSecuredPointcut
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Aop\Pointcut
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class TokenSecuredPointcut implements PointcutInterface
{
    /** @var Reader */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesClass(\ReflectionClass $class)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function matchesMethod(\ReflectionMethod $method)
    {
        return null !== $this->reader->getMethodAnnotation(
            $method,
            '\TokenSecuredAction\Annotation\TokenSecured'
        );
    }
}
