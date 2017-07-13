<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Features\Context;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Class ConfigStoreApiClient
 *
 * @package \ConfigStore\Features\Context
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class ConfigStoreApiClient implements ClientInterface
{
    /** @var ClientInterface */
    private $wrappedClient;
    /** @var RequestInterface */
    private $lastSendRequest;
    /** @var ResponseInterface */
    private $lastReceivedResponse;

    /**
     * @param ClientInterface $wrappedClient
     */
    public function __construct(ClientInterface $wrappedClient)
    {
        $this->wrappedClient = $wrappedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest($method, $url = null, array $options = [])
    {
        return $this->wrappedClient->createRequest($method, $url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function get($url = null, $options = [])
    {
        return $this->wrappedClient->get($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function head($url = null, array $options = [])
    {
        return $this->wrappedClient->head($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($url = null, array $options = [])
    {
        return $this->wrappedClient->delete($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function put($url = null, array $options = [])
    {
        return $this->wrappedClient->put($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($url = null, array $options = [])
    {
        return $this->wrappedClient->patch($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function post($url = null, array $options = [])
    {
        return $this->wrappedClient->post($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function options($url = null, array $options = [])
    {
        return $this->wrappedClient->options($url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        $this->lastSendRequest      = $request;
        $this->lastReceivedResponse = $this->wrappedClient->send($request);

        return $this->lastReceivedResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function sendAll($requests, array $options = [])
    {
        return $this->wrappedClient->sendAll($requests, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption($keyOrPath = null)
    {
        return $this->wrappedClient->getDefaultOption($keyOrPath);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOption($keyOrPath, $value)
    {
        return $this->wrappedClient->setDefaultOption($keyOrPath, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->wrappedClient->getBaseUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmitter()
    {
        return $this->wrappedClient->getEmitter();
    }

    /**
     * @return RequestInterface
     */
    public function getLastSendRequest()
    {
        return $this->lastSendRequest;
    }

    /**
     * @return ResponseInterface
     */
    public function getLastReceivedResponse()
    {
        return $this->lastReceivedResponse;
    }
}
