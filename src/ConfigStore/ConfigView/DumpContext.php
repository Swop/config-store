<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView;

use ConfigStore\Model\App;

/**
 * Class DumpContext
 *
 * @package \ConfigStore\ConfigView
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class DumpContext
{
    /** @var array */
    private $envVars;
    /** @var array */
    private $envVarPlaceholders;
    /** @var array */
    private $configPlaceholders;
    /** @var array */
    private $placeholders;
    /** @var array */
    private $initialAppConfigs;
    /** @var array */
    private $dumpOptions;
    /** @var App */
    private $app;

    /**
     * @param App   $app
     * @param array $dumpOption
     * @param array $envVars
     */
    public function __construct(App $app, array $dumpOption = [], array $envVars = [])
    {
        $this->app               = $app;
        $this->initialAppConfigs = $app->getConfigArray();
        $this->envVars           = $envVars;
        $this->dumpOptions       = $dumpOption;

        $this->envVarPlaceholders = $this->mapToPlaceHolders($envVars);
        $this->configPlaceholders = $this->mapToPlaceHolders($this->initialAppConfigs);

        $this->placeholders = array_merge($this->configPlaceholders, $this->envVarPlaceholders);
    }

    /**
     * @return array
     */
    public function getDumpOptions()
    {
        return $this->dumpOptions;
    }

    /**
     * @return array
     */
    public function getEnvVarPlaceholders()
    {
        return $this->envVarPlaceholders;
    }

    /**
     * @return array
     */
    public function getConfigPlaceholders()
    {
        return $this->configPlaceholders;
    }

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @return array
     */
    public function getInitialAppConfigs()
    {
        return $this->initialAppConfigs;
    }

    /**
     * Re-key the input array with {ENV_VAR_NAME} keys instead of ENV_VAR_NAME ones.
     *
     * @param array $data
     *
     * @return array
     */
    private function mapToPlaceHolders(array $data)
    {
        return array_combine(
            array_map(
                function ($key) {
                    return '{' . $key . '}';
                },
                array_keys($data)
            ),
            array_values($data)
        );
    }
}
