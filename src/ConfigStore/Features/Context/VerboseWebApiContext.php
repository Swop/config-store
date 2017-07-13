<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Features\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\WebApiExtension\Context\WebApiContext;
use GuzzleHttp\ClientInterface;
use PHPUnit_Framework_Assert as Assertions;
use Symfony\Component\Yaml\Yaml;

/**
 * Class VerboseWebApiContext
 *
 * @package \ConfigStore\Features\Context
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class VerboseWebApiContext extends WebApiContext
{
    /** @var ConfigStoreApiClient */
    private $wrappedClient;

    /**
     * {@inheritdoc}
     */
    public function setClient(ClientInterface $client)
    {
        $this->wrappedClient = new ConfigStoreApiClient($client);

        parent::setClient($this->wrappedClient);
    }

    /**
     * {@inheritDoc}
     */
    public function theResponseCodeShouldBe($code)
    {
        try {
            parent::theResponseCodeShouldBe($code);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $this->printResponse();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        try {
            parent::theResponseShouldContainJson($jsonString);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $this->printResponse();
            throw $e;
        }
    }

    /**
     * Checks that response body contains Yaml from PyString.
     *
     * Do not check that the response body /only/ contains the YAML from PyString,
     *
     * @param PyStringNode $yamlString
     *
     * @throws \RuntimeException
     *
     * @Then /^(?:the )?response should contain yaml:$/
     */
    public function theResponseShouldContainYaml(PyStringNode $yamlString)
    {
        $etalon = Yaml::parse($this->replacePlaceHolder($yamlString->getRaw()));
        $actual = Yaml::parse($this->wrappedClient->getLastReceivedResponse()->getBody()->getContents());

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to yaml:\n" . $this->replacePlaceHolder($yamlString->getRaw())
            );
        }

        try {
            Assertions::assertGreaterThanOrEqual(count($etalon), count($actual));
            foreach ($etalon as $key => $needle) {
                Assertions::assertArrayHasKey($key, $actual);
                Assertions::assertEquals($etalon[$key], $actual[$key]);
            }
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $this->printResponse();
            throw $e;
        }
    }

    /**
     * Checks that response body is equals to the given PyString.
     *
     * @param PyStringNode $responseBody
     *
     * @throws \RuntimeException
     *
     * @Then /^(?:the )?response should be:$/
     */
    public function theResponseShouldBe(PyStringNode $responseBody)
    {
        Assertions::assertEquals(
            $responseBody->getRaw(),
            $this->wrappedClient->getLastReceivedResponse()->getBody()->getContents()
        );
    }

    /**
     * @Given /^the imported PHP script should match with the following JSON object:$/
     *
     * @param PyStringNode $jsonObject
     */
    public function theImportedPHPScriptShouldMatchWithTheFollowingJSONObject(PyStringNode $jsonObject)
    {
        $response = $this->wrappedClient->getLastReceivedResponse()->getBody()->getContents();

        $etalon = json_decode($this->replacePlaceHolder($jsonObject->getRaw()), true);
        $actual = eval('?>'.$response);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n" . $this->replacePlaceHolder($jsonObject->getRaw())
            );
        }

        Assertions::assertGreaterThanOrEqual(count($etalon), count($actual));
        foreach ($etalon as $key => $needle) {
            Assertions::assertArrayHasKey($key, $actual);
            Assertions::assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * @Given /^the following constants must be defined:$/
     *
     * @param TableNode $constants
     */
    public function theFollowingConstantsMustBeDefined(TableNode $constants)
    {
        foreach ($constants->getHash() as $constantHash) {
            switch($constantHash['type']) {
                default:
                case 'string':
                    $value = $constantHash['value'];
                    break;
                case 'integer':
                    $value = (int)$constantHash['value'];
                    break;
                case 'float':
                    $value = (float)$constantHash['value'];
                    break;
                case 'null':
                    $value = null;
                    break;
                case 'boolean':
                    $value = (bool)$constantHash['value'];
            }
            Assertions::assertTrue(defined($constantHash['name']));
            Assertions::assertSame($value, constant($constantHash['name']));
        }
    }

    /**
     * @Given /^I remove all headers$/
     */
    public function iRemoveAllHeaders()
    {
        foreach (array_keys($this->getHeaders()) as $headerName) {
            $this->removeHeader($headerName);
        }
    }
}
