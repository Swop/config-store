<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer;

use ConfigStore\ConfigView\DumpContext;
use ConfigStore\ConfigView\Transformer\ConfigValueTransformer;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ArrayValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\BooleanValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ConfigValueType;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NullValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NumberValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\StringValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\Parser\ConfigValueParser;

/**
 * Class ParserConfigValueTransformer
 *
 * @package \ConfigStore\ConfigView\Transformer\Parser
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ParserConfigValueTransformer implements ConfigValueTransformer
{
    /**
     * @param ConfigValueParser $parser
     */
    public function __construct(ConfigValueParser $parser)
    {
        $this->parser = $parser;
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

        try {
            $AST = $this->parser->getAST($value);
            $value = $this->buildValueFromAST($AST);
        } catch (\Exception $e) {
        }

        return $value;
    }

    /**
     * @param ConfigValueType $AST
     *
     * @return mixed
     */
    private function buildValueFromAST(ConfigValueType $AST)
    {
        if ($AST instanceof NullValue) {
            $value = null;
        } elseif ($AST instanceof ArrayValue) {
            $value = [];

            foreach ($AST->getParts() as $part) {
                $value[] = $this->buildValueFromAST($part);
            }
        } elseif ($AST instanceof BooleanValue
            || $AST instanceof NumberValue
            || $AST instanceof StringValue
        ) {
            $value = $AST->getValue();
        } else {
            throw new \InvalidArgumentException('Unsupported config value type');
        }

        return $value;
    }
}
