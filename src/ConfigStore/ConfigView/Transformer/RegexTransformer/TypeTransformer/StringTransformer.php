<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use ConfigStore\ConfigView\DumpContext;

class StringTransformer implements ConfigTypeTransformer
{
    /**
     * {@inheritDoc}
     */
    public function matchConfigStringValue($configValue)
    {
        return preg_match('/^".*"$/', trim($configValue));
    }

    /**
     * {@inheritDoc}
     */
    public function transformToRealType($configValue, DumpContext $dumpContext)
    {
        $configValue = trim($configValue);

        preg_match('/^"(.*)"$/', $configValue, $matches);
        $configValue = $matches[1];

        $configValue = stripslashes($configValue);

        return (string)$configValue;
    }
}

