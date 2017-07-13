<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use ConfigStore\ConfigView\DumpContext;

class BooleanTransformer implements ConfigTypeTransformer
{
    /**
     * {@inheritDoc}
     */
    public function matchConfigStringValue($configValue)
    {
        return in_array(trim($configValue), ['true', 'false'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function transformToRealType($configValue, DumpContext $dumpContext)
    {
        $configValue = trim($configValue);

        switch ($configValue) {
            case "true":
                $configValue = true;
                break;
            case "false":
                $configValue = false;
                break;
            default:
                $configValue = (string) $configValue;
        }
        return $configValue;
    }
}
