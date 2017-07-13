<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use ConfigStore\ConfigView\DumpContext;

class NumberTransformer implements ConfigTypeTransformer
{
    /**
     * {@inheritDoc}
     */
    public function matchConfigStringValue($configValue)
    {
        return preg_match('/^#[0-9]+(?:\.[0-9]+)?$/', trim($configValue));
    }

    /**
     * {@inheritDoc}
     */
    public function transformToRealType($configValue, DumpContext $dumpContext)
    {
        $configValue = trim($configValue);

        preg_match('/^#([0-9]+(?:\.[0-9]+)?)$/', $configValue, $matches);

        if (isset($matches[1]) && false !== $number = filter_var($matches[1], FILTER_VALIDATE_FLOAT)) {
            if (ctype_digit((string)$number)) {
                return (int)$matches[1];
            } else {
                return (float)$matches[1];
            }
        }

        return (string)$configValue;
    }
}
