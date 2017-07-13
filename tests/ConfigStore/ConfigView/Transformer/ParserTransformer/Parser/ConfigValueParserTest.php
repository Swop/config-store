<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Test\ConfigView\Transformer\ParserTransformer\Parser;

use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ArrayValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\BooleanValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\ConfigValueType;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NullValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\NumberValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\ConfigValueTypes\StringValue;
use ConfigStore\ConfigView\Transformer\ParserTransformer\Parser\ConfigValueLexer;
use ConfigStore\ConfigView\Transformer\ParserTransformer\Parser\ConfigValueParser;

/**
 * Class ConfigValueParserTest
 *
 * @package ConfigStore\Test\ConfigView\Transformer\ParserTransformer\Parser
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ConfigValueParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parsingDataProvider
     *
     * @param string    $input
     * @param ConfigValueType $expectedValueTypeObject
     */
    public function testParsing($input, $expectedValueTypeObject)
    {
        $parser = new ConfigValueParser(new ConfigValueLexer());

        $ast = $parser->getAST($input);

        $this->assertEquals($expectedValueTypeObject, $ast);
    }

    /**
     * @dataProvider invalidParsingDataProvider
     *
     * @expectedException \InvalidArgumentException
     *
     * @param string    $input
     */
    public function testInvalidParsing($input)
    {
        $parser = new ConfigValueParser(new ConfigValueLexer());

        $parser->getAST($input);
    }

    /**
     * @return array
     */
    public function parsingDataProvider()
    {
        # Numbers
        $numbers = [
            ['#123', new NumberValue(123)],
            ['#123.45', new NumberValue(123.45)],
        ];

        # Strings
        $strings = [
            ['This is a simple text', new StringValue('This is a simple text')],
            ['I\'m the better one', new StringValue('I\'m the better one')],
            ['I\'m the "better" one', new StringValue('I\'m the "better" one')],
            ['"I\'m the "better" one"', new StringValue('I\'m the "better" one')],
            ['This is not [an array]', new StringValue('This is not [an array]')],
            ['I am not a number #123', new StringValue('I am not a number #123')],
            ['"#123"', new StringValue('#123')],
            ['123', new StringValue('123')],
            ['2013-01-09 00:00:00', new StringValue('2013-01-09 00:00:00')],
            ['"2013-01-09 00:00:00"', new StringValue('2013-01-09 00:00:00')],
            ['"ch[all,#1"', new StringValue('ch[all,#1')],
            ['"chall,enge"', new StringValue('chall,enge')],
            ['"chall"enge"', new StringValue('chall"enge')],
            ['"chall\"enge"', new StringValue('chall"enge')],
            ['aa,aa"aa"a', new StringValue('aa,aa"aa"a')],
            ['"a]a\"a,a["', new StringValue('a]a"a,a[')],
            ['"a]a\"a,a[', new StringValue('a]a"a,a[')],
        ];

        # Boolans
        $booleans = [
            ['true', new BooleanValue(true)],
            ['TRUE', new BooleanValue(true)],
            ['false', new BooleanValue(false)],
            ['FALSE', new BooleanValue(false)],
        ];

        # Nulls
        $nulls = [
            ['null', new NullValue()],
            ['NULL', new NullValue()],
        ];

        # Arrays
        $arrays = [
            ['[]', new ArrayValue([])],
            ['[a]', new ArrayValue([new StringValue('a')])],
            ['["a"]', new ArrayValue([new StringValue('a')])],
            ['[#1, #2, #3]', new ArrayValue([new NumberValue(1), new NumberValue(2), new NumberValue(3)])],
            ['[a, b]', new ArrayValue([new StringValue('a'), new StringValue('b')])],
            ['[a,b]', new ArrayValue([new StringValue('a'), new StringValue('b')])],
            ['[a ,b]', new ArrayValue([new StringValue('a'), new StringValue('b')])],
            ['[#1,"b\"]', new ArrayValue([new NumberValue(1), new StringValue('b\\')])],
            ['[#1,"b\"c"]', new ArrayValue([new NumberValue(1), new StringValue('b"c')])],
            [
                '["a",["1",#2], null, ["chall,enge"]]',
                new ArrayValue([
                    new StringValue('a'),
                    new ArrayValue([
                        new StringValue('1'),
                        new NumberValue(2)
                    ]),
                    new NullValue(),
                    new ArrayValue([
                        new StringValue('chall,enge')
                    ])
                ])
            ],
            ['["chall,enge"]', new ArrayValue([new StringValue('chall,enge')])],
            ['[aa,a,"a[a,",a]', new ArrayValue([
                new StringValue('aa'),
                new StringValue('a'),
                new StringValue('a[a,'),
                new StringValue('a'),
            ])],
            ['["I\'m a text"]', new ArrayValue([new StringValue("I'm a text")])],
            [
                '["I\'m a text", "with an intern", ["array", "yeah !"]]',
                new ArrayValue([
                    new StringValue("I'm a text"),
                    new StringValue("with an intern"),
                    new ArrayValue([new StringValue('array'), new StringValue('yeah !')])
                ])
            ],
        ];

        return array_merge($numbers, $strings, $booleans, $nulls, $arrays);
    }

    /**
     *
     * @return array
     */
    public function invalidParsingDataProvider()
    {
        return [
            ['[#1,"b\"c]'],
        ];
    }
}
