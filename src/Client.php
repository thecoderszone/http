<?php
/**
 * Copyright Paul Adams, 2020. All rights reserved.
 * Unauthorized reproduction is prohibited.
 *
 * @package Dashboard
 * @author Paul Adams <paul@thecoderszone.com>
 */

namespace TCZ\Http;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP client class.
 *
 * @method Response get(string $uri, array $options = [])
 * @method Response head(string $uri, array $options = [])
 * @method Response put(string $uri, array $options = [])
 * @method Response post(string $uri, array $options = [])
 * @method Response patch(string $uri, array $options = [])
 * @method Response delete(string $uri, array $options = [])
 *
 * @package TCZ\Http
 */
class Client
{
    /**
     * The base url.
     *
     * @var string
     */
    protected $baseUrl;
    
    /**
     * The response callback.
     *
     * @var callable
     */
    protected $callback;
    
    /**
     * The Guzzle instance.
     *
     * @var Guzzle
     */
    protected $client;
    
    /**
     * Instantiate the client.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? null;
        unset($config['base_url']);
        
        $this->callback = $config['callback'] ?? null;
        unset($config['callback']);
        
        $this->client = new Guzzle($config);
    }
    
    /**
     * Make a request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $config
     *
     * @return Response
     * @throws GuzzleException
     */
    public function request($method, $endpoint = null, array $config = [])
    {
        try {
            $response = $this->client->request($method, $this->baseUrl.$endpoint, $config);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        }
        
        $response = $this->parseResponse($response);
        
        if ($this->callback) {
            ($this->callback)($response);
        }
        
        return $response;
    }
    
    /**
     * Get the response.
     *
     * @param ResponseInterface $response
     *
     * @return Response
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeader('Content-Type')[0] ?? null;
        
        if (strpos($contentType, 'application/json') !== false) {
            return new JsonResponse($response);
        }
        
        return new Response($response);
    }
    
    /**
     * Magic method to make a request.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return JsonResponse|Response
     * @throws GuzzleException
     */
    public function __call($method, $arguments)
    {
        return $this->request(strtoupper($method), ...$arguments);
    }
}
