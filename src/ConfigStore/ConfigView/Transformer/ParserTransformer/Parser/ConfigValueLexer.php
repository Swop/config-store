<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\Parser;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * Class ConfigValueLexer
 *
 * @package \ConfigStore\ConfigView\Parser
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ConfigValueLexer extends AbstractLexer
{
    const T_NONE = 1;
    const T_STRING = 2;
    const T_NUMBER = 3;
    const T_BOOLEAN = 4;
    const T_NULL = 5;
    const T_OPEN_SQUARE_BRACKET = 6;
    const T_CLOSED_SQUARE_BRACKET = 7;
    const T_COMMA = 8;

    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected function getCatchablePatterns()
    {
        return [
            '^#[0-9]+(?:\.[0-9]+)?$', // Numbers
            '^true|false$',           // boolean
            '^null$',                 // NULL
            '^"(?:[^"]|\\")*"$',      // strings
            '\[|\]|,',                // array brackets and comma
            '^.*$',             // string fallback
//            '^[^#"\[]*$',             // string fallback
        ];
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns()
    {
        return ['\s+'];
//        return [];
    }

    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     *
     * @return integer
     */
    protected function getType(&$value)
    {
        $lowerCaseValue = strtolower($value);

        if ($value[0] === '#') {
            $numberValue = substr($value, 1, strlen($value) - 1);

            if (false !== $numberValue = filter_var($numberValue, FILTER_VALIDATE_FLOAT)) {
                return self::T_NUMBER;
            }
        }

        if ($value[0] === '"') {
            return self::T_STRING;
        }


        if ($lowerCaseValue === 'true' || $lowerCaseValue === 'false') {
            switch ($lowerCaseValue) {
                case "true":
                    $value = true;
                    break;
                case "false":
                    $value = false;
                    break;
            }
            return self::T_BOOLEAN;
        }

        if ($lowerCaseValue === 'null') {
            $value = null;
            return self::T_NULL;
        }

        if ($value === '[') {
            return self::T_OPEN_SQUARE_BRACKET;
        }

        if ($value === ']') {
            return self::T_CLOSED_SQUARE_BRACKET;
        }

        if ($value === ',') {
            return self::T_COMMA;
        }

        return self::T_STRING;
    }
}
