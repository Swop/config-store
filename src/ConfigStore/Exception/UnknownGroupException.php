<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Exception;

class UnknownGroupException extends \RuntimeException
{
    /** @var int $groupId */
    protected $groupId;

    /**
     * @param int        $groupId
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($groupId, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("Unknown group with id %s", $groupId);

        parent::__construct($message, $code, $previous);

        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
