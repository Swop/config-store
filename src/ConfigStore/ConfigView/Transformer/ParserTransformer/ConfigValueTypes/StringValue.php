<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes;

/**
 * Class StringValue
 *
 * @package \ConfigStore\ConfigView\ConfigValueTypes
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class StringValue extends ConfigValueType
{
    /** @var string */
    private $value;

    /**
     * @param string $value
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
