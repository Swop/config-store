<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Transformer\ParserTransformer\Parser;

use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ArrayValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\BooleanValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ConfigValueType;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NullValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NumberValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\StringValue;

/**
 * Class ConfigValueParser
 *
 * @package \ConfigStore\ConfigView\Parser
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ConfigValueParser
{
    /** @var ConfigValueLexer */
    private $lexer;
    /** @var bool */
    private $inArray = false;
    /** @var string */
    private $input;
    /** @var int */
    private $inputLength;

    /**
     * @param ConfigValueLexer $lexer
     */
    public function __construct(ConfigValueLexer $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param $input
     *
     * @return ConfigValueType
     */
    public function getAST($input)
    {
        $this->reset();
        $this->input       = $input;
        $this->inputLength = strlen($input);
        $this->lexer->setInput($input);

        return $this->configLanguage();
    }

    private function reset()
    {
        $this->inArray = false;
        $this->input   = null;
    }

    /**
     * @return ConfigValueType
     */
    private function configLanguage()
    {
        $this->lexer->moveNext();

        $value = $this->value();

        // Check for end of string
        if ($this->lexer->lookahead !== null) {
            $this->syntaxError('End of string');
        }

        return $value;
    }

    /**
     * @return ConfigValueType
     */
    private function value()
    {
        $token = $this->lexer->lookahead;

        switch ($token['type']) {
            case ConfigValueLexer::T_NUMBER:
                $value = $this->numberValue();
                break;
            case ConfigValueLexer::T_STRING:
                $value = $this->stringValue();
                break;
            case ConfigValueLexer::T_BOOLEAN:
                $value = $this->booleanValue();
                break;
            case ConfigValueLexer::T_NULL:
                $value = $this->nullValue();
                break;
            case ConfigValueLexer::T_OPEN_SQUARE_BRACKET:
                $value = $this->arrayValue();
                break;
            default:
                $value = null;
                $this->syntaxError('Invalid value');
                break;
        }

        $this->lexer->moveNext();

        return $value;
    }

    /**
     * @return NumberValue
     */
    private function numberValue()
    {
        $token = $this->lexer->lookahead;

        // Removes the '#'
        $numberValue = substr($token['value'], 1, strlen($token['value']) - 1);

        if (ctype_digit((string)$numberValue)) {
            $numberValue = (int)$numberValue;
        } else {
            $numberValue = (float)$numberValue;
        }

        return new NumberValue($numberValue);
    }

    /**
     * @return StringValue
     */
    private function stringValue()
    {
        $token = $this->lexer->lookahead;

        $beginningQuotes = false;
        if ($token['value'][0] === '"') {
            $beginningQuotes = true;
            $stringValue = substr($token['value'], 1, strlen($token['value']) - 1);
        } else {
            $stringValue = $token['value'];
        }

        $endingQuotes = false;
        if ($stringValue[strlen($stringValue) - 1] === '"') {
            $endingQuotes = true;
            $stringValue = substr($stringValue, 0, strlen($stringValue) - 1);
        }

        if (!$this->inArray) {
            // If we're not in an encompassing array, everything after the string token must be considered
            // as member of the string.
            // ex:
            //  "abab",ded"
            //  ged,ded

            do {
                $this->lexer->moveNext();
                $token = $this->lexer->lookahead;

                if ($token === null) {
                    break;
                }

                $stringValue .= $token['value'];
            } while (true);
        } elseif ($beginningQuotes && !$endingQuotes) {
            // If the string is cut by other tokens, like comma, but is encompassed with quotes, all the following
            // tokens must be included until a string with a following quote is found.

            // ex:
            //  ["abab,ded"]

            do {
                $position = $token['position'] + strlen($token['value']);

                // Since the "\s+" are squeze during lexing step, we try to gather the used space here
                // to authorize string (with spaces) without encompassing quotes, like: [hello there!]
                while ($position < $this->inputLength && ' ' === $char = $this->input[$position]) {
                    $stringValue .= $char;
                    $position += 1;
                }

                $this->lexer->moveNext();
                $token = $this->lexer->lookahead;

                if ($token === null) {
                    break;
                }

                if ($token['type'] === ConfigValueLexer::T_STRING
                    && $token['value'][strlen($token['value']) - 1] === '"'
                ) {
                    $stringValue .= $token['value'];
                    $stringValue = substr($stringValue, 0, strlen($stringValue) - 1);

                    break;
                }

                $stringValue .= $token['value'];
            } while (true);
        }

        // Replace all \" with "
        $stringValue = str_replace("\\\"", '"', $stringValue);

        return new StringValue($stringValue);
    }

    /**
     * @return BooleanValue
     */
    private function booleanValue()
    {
        $token = $this->lexer->lookahead;

        return new BooleanValue($token['value']);
    }

    /**
     * @return NullValue
     */
    private function nullValue()
    {
        return new NullValue();
    }

    /**
     * @return ArrayValue
     */
    private function arrayValue()
    {
        // Skip the open bracket
        $this->lexer->moveNext();

        $arrayValue    = new ArrayValue();
        $this->inArray = true;

        while ($this->lexer->lookahead['type'] !== ConfigValueLexer::T_CLOSED_SQUARE_BRACKET) {
            if ($this->lexer->lookahead['type'] === ConfigValueLexer::T_COMMA) {
                $peek = $this->lexer->glimpse();
                if (count($arrayValue->getParts()) === 0
                    || ($peek !== null && $peek['type'] === ConfigValueLexer::T_CLOSED_SQUARE_BRACKET)) {
                    // Comma at first position of an array or at the end of it
                    $this->syntaxError('T_VALUE');
                }

                $this->lexer->moveNext();
            }

            $arrayValue->addPart($this->value());
            if ($this->lexer->lookahead === null) {
                // If no square brackets is found
                $this->syntaxError('T_CLOSED_SQUARE_BRACKET');
            }
        }

        $this->inArray = false;

        return $arrayValue;
    }

    /**
     * Generates a new syntax error.
     *
     * @param string      $expected Expected string.
     * @param array|null  $token    Got token.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = (isset($token['position'])) ? $token['position'] : '-1';
        $message  = '"' . $this->input . "\" line 0, col {$tokenPos}: Error: ";
        $message .= ($expected !== '') ? "Expected {$expected}, got " : 'Unexpected ';
        $message .= ($this->lexer->lookahead === null) ? 'end of string.' : "'{$token['value']}'";

        throw new \InvalidArgumentException($message);
    }
}
