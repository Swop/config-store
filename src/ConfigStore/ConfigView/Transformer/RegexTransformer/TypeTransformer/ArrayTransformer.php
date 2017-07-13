<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer;

use ConfigStore\ConfigView\DumpContext;
use ConfigStore\ConfigView\Transformer\ConfigValueTransformer;

class ArrayTransformer implements ConfigTypeTransformer
{
    /** @var ConfigValueTransformer */
    private $transformerManager;

    public function __construct(ConfigValueTransformer $transformerManager)
    {
        $this->transformerManager = $transformerManager;
    }

    /**
     * {@inheritDoc}
     */
    public function matchConfigStringValue($configValue)
    {
        return preg_match('/^\[(?:(?:[^,]+)(?:,[^,]+)*)?\]$/', trim($configValue));
    }

    /**
     * {@inheritDoc}
     */
    public function transformToRealType($configValue, DumpContext $dumpContext)
    {
        preg_match('/^\[((?:[^,]+)(?:,[^,]+)*)?\]$/', trim($configValue), $matches);

        $array = [];
        if (isset($matches[1])) {
            $subValues = explode(',', $matches[1]);
            foreach ($subValues as $subValue) {
                $array[] = $this->transformerManager->transform($subValue, $dumpContext);
            }
        }

        return $array;
    }
}
