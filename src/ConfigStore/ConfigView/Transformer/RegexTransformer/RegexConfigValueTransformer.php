<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\RegexTransformer;

use ConfigStore\ConfigView\DumpContext;
use ConfigStore\ConfigView\Transformer\ConfigValueTransformer;
use ConfigStore\ConfigView\Transformer\RegexTransformer\TypeTransformer\ConfigTypeTransformer;

class RegexConfigValueTransformer implements ConfigValueTransformer
{
    /** @var ConfigTypeTransformer[] */
    private $transformers = [];
    /** @var ConfigTypeTransformer[] */
    private $sorted = [];

    public function registerTransformer(ConfigTypeTransformer $transformer, $priority = 0)
    {
        $this->transformers[$priority][] = $transformer;
        $this->sorted                    = null;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value, DumpContext $dumpContext)
    {
        $placeholders = $dumpContext->getPlaceholders();

        if (!empty($placeholders)) {
            $value = strtr($value, $placeholders);
        }

        foreach ($this->getTransformers() as $transformer) {
            if ($transformer->matchConfigStringValue($value)) {
                return $transformer->transformToRealType($value, $dumpContext);
            }
        }

        return trim($value);
    }

    public function getTransformers()
    {
        if ($this->sorted === null) {
            $this->sortTransformers();
        }

        return $this->sorted;
    }

    /**
     * Sorts the internal list of transformers by priority.
     */
    private function sortTransformers()
    {
        $this->sorted = [];

        krsort($this->transformers);
        $this->sorted = call_user_func_array('array_merge', $this->transformers);
    }
}
