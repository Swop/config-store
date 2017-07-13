<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use \ConfigStore\ConfigView\DumpContext;

class NullTransformer implements ConfigTypeTransformer
{
    /**
     * {@inheritDoc}
     */
    public function matchConfigStringValue($configValue)
    {
        return trim(strtolower($configValue)) === 'null';
    }

    /**
     * {@inheritDoc}
     */
    public function transformToRealType($configValue, DumpContext $dumpContext)
    {
        $configValue = trim(strtolower($configValue));

        return $configValue === 'null' ? null : (string)$configValue;
    }
}
