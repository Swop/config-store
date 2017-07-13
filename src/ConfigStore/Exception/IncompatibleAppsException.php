<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Exception;

use Exception;
use ConfigStore\Model\App;

class IncompatibleAppsException extends \RuntimeException
{
    /** @var App[] */
    private $apps;

    /**
     * @param App       $app1
     * @param App       $app2
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct(App $app1, App $app2, $code = 0, Exception $previous = null)
    {
        parent::__construct(
            "Incompatible apps. Can't obtain a diff for apps which aren't in the same group.",
            $code,
            $previous
        );

        $this->apps[] = $app1;
        $this->apps[] = $app2;
    }

    /**
     * @return array
     */
    public function getApps()
    {
        return $this->apps;
    }
}
