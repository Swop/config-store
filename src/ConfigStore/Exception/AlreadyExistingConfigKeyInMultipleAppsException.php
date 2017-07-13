<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Exception;

use ConfigStore\Model\App;
use ConfigStore\Model\ConfigItem;

/**
 * Class AlreadyExistingConfigKeyInMultipleAppsException
 *
 * @package \ConfigStore\Exception
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class AlreadyExistingConfigKeyInMultipleAppsException extends \RuntimeException
{
    /** @var ConfigItem $configItem */
    protected $configItem;
    /** @var App[] $applications */
    protected $applications;

    /**
     * @param ConfigItem $configItem
     * @param App[]      $applications
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(ConfigItem $configItem, array $applications, $code = 0, \Exception $previous = null)
    {
        $message = sprintf(
            "Some applications already have the config key %s",
            $configItem->getKey()
        );

        parent::__construct($message, $code, $previous);

        $this->configItem   = $configItem;
        $this->applications = $applications;
    }

    /**
     * @return App[]
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * @return ConfigItem
     */
    public function getConfigItem()
    {
        return $this->configItem;
    }
}
