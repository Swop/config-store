<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes;

/**
 * Class NumberValue
 *
 * @package \ConfigStore\ConfigView\ConfigValueTypes
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class NumberValue extends ConfigValueType
{
    /** @var int|float */
    private $value;

    /**
     * @param int|float $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return float|int
     */
    public function getValue()
    {
        return $this->value;
    }
}
