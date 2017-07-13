<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Exception;

class UnknownUserException extends \RuntimeException
{
    /** @var mixed $userIdentifier */
    protected $userIdentifier;

    /**
     * @param mixed      $userIdentifier
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($userIdentifier, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("Unknown user %s", $userIdentifier);

        parent::__construct($message, $code, $previous);

        $this->userIdentifier = $userIdentifier;
    }

    /**
     * Gets the userIdentifier attribute
     *
     * @return mixed
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }
}
