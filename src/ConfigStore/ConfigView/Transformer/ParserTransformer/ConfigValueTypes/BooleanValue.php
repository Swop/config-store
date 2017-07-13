<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes;

/**
 * Class BooleanValue
 *
 * @package \ConfigStore\ConfigView\ConfigValueTypes
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class BooleanValue extends ConfigValueType
{
    /** @var bool */
    private $value;

    /**
     * @param bool $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
