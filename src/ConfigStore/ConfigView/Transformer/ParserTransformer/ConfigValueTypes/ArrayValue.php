<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes;

/**
 * Class ArrayValue
 *
 * @package \ConfigStore\ConfigView\ConfigValueTypes
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ArrayValue extends ConfigValueType
{
    /** @var ConfigValueType[] */
    private $parts;

    /**
     * Constructor
     */
    public function __construct(array $parts = [])
    {
        $this->parts = $parts;
    }

    /**
     * @param ConfigValueType $part
     */
    public function addPart(ConfigValueType $part)
    {
        $this->parts[] = $part;
    }

    /**
     * @return ConfigValueType[]
     */
    public function getParts()
    {
        return $this->parts;
    }
}
