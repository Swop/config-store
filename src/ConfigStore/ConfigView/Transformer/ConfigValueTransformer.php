<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer;

use ConfigStore\ConfigView\DumpContext;

/**
 * Interface ConfigValueTransformer
 *
 * @package \ConfigStore\ConfigView\Transformer
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
interface ConfigValueTransformer
{
    /**
     * Transform a config value to its real type
     *
     * @param string      $value
     * @param DumpContext $dumpContext
     *
     * @return string
     */
    public function transform($value, DumpContext $dumpContext);
}
