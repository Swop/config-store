<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace TokenSecuredAction\Annotation;

/**
 * Class TokenSecured
 *
 * @package \TokenSecuredAction\Annotation
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 *
 * @Annotation
 */
class TokenSecured
{
    /** Default intention */
    const DEFAULT_INTENTION = 'default';

    /** @var string */
    private $intention;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['value'])) {
            $options['intention'] = $options['value'];
            unset($options['value']);
        }

        if (!isset($options['intention'])) {
            $options['intention'] = self::DEFAULT_INTENTION;
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getIntention()
    {
        return $this->intention;
    }
}
