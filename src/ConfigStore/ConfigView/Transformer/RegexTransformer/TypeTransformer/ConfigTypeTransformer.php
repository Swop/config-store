<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use ConfigStore\ConfigView\DumpContext;

interface ConfigTypeTransformer
{
    /**
     * Checks if the apadter supports the given format
     *
     * @param string $configValue
     *
     * @return bool
     */
    public function matchConfigStringValue($configValue);

    /**
     * Transform a config value string representation to its real type
     *
     * @param string      $configValue
     * @param DumpContext $dumpContext
     *
     * @return mixed
     */
    public function transformToRealType($configValue, DumpContext $dumpContext);
}
