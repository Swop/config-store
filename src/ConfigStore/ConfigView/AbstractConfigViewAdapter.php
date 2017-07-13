<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView;

use ConfigStore\ConfigView\Transformer\ConfigValueTransformer;

abstract class AbstractConfigViewAdapter implements ConfigViewAdapter
{
    /** @var ConfigValueTransformer */
    protected $configValueTransformer;

    /**
     * @param ConfigValueTransformer $configValueTransformer
     */
    public function setConfigValueTransformer(ConfigValueTransformer $configValueTransformer)
    {
        $this->configValueTransformer = $configValueTransformer;
    }

    protected function getTransformedAppConfigValues(DumpContext $dumpContext)
    {
        $configs = $dumpContext->getInitialAppConfigs();

        if (null === $this->configValueTransformer) {
            return $configs;
        }

        $transformedConfig = [];

        foreach ($configs as $key => $value) {
            $transformedConfig[$key] = $this->configValueTransformer->transform(
                $value,
                $dumpContext
            );
        }

        return $transformedConfig;
    }
}
