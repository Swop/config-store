<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Exception;

class UnknownAppException extends \RuntimeException
{
    /** @var string|int $appIdentifier */
    protected $appIdentifier;

    /**
     * @param string|int $appIdentifier
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($appIdentifier, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("Unknown application %s", $appIdentifier);

        parent::__construct($message, $code, $previous);

        $this->appIdentifier = $appIdentifier;
    }

    /**
     * @return string|int
     */
    public function getAppIdentifier()
    {
        return $this->appIdentifier;
    }
}
