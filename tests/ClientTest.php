<?php
/**
 * Copyright Paul Adams, 2020. All rights reserved.
 * Unauthorized reproduction is prohibited.
 *
 * @package Dashboard
 * @author Paul Adams <paul.adams@thecoderszone.com>
 */

namespace Tests;

use TCZ\Http\Client;
use TCZ\Http\JsonResponse;
use TCZ\Http\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use AssertsRequests;
    
    /**
     * @var HandlerStack
     */
    protected $handler;
    
    /**
     * @var MockHandler
     */
    protected $server;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createHandler();
    }
    
    public function test_request_returns_response()
    {
        $this->server->append(
            new GuzzleResponse(
                $statusCode = 200,
                $headers = [
                    'Content-Type' => $contentType = 'text/html',
                    'Server' => $server = [
                        'Apache',
                        'The Coders Zone',
                    ],
                ],
                $body = '<html lang="en"><p>Hello, World!</p></html>'
            )
        );
        
        $client = new Client([
            'handler' => $this->handler,
        ]);
        
        $response = $client->request('GET', '/test-url');
        
        $this->assertRequestSent('GET', '/test-url');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(1.1, $response->getProtocolVersion());
        $this->assertEquals($response, $response->setProtocolVersion($version = 2.0));
        $this->assertEquals($version, $response->getProtocolVersion());
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($response, $response->setStatus($code = 400, $phrase = 'Bad request'));
        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals($phrase, $response->getReasonPhrase());
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals($headers, $response->getHeaders());
        $this->assertEquals($contentType, $response->getHeader('content-type'));
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertEquals(implode(', ', $server), $response->getHeaderLine('server'));
        $this->assertEquals($response, $response->setHeader('SERVER', $server = 'no'));
        $this->assertEquals($server, $response->getHeader('sErVeR'));
        $this->assertEquals($response, $response->appendHeader('Content-Type', 'text/plain'));
        $this->assertIsArray($response->getHeader('content-TYPE'));
        $this->assertCount(2, $response->getHeader('content-TYPE'));
        $this->assertEquals($response, $response->removeHeader('server'));
        $this->assertFalse($response->hasHeader('Server'));
        $this->assertEquals($body, $response->getBody());
        $this->assertNull($response->foo);
        $this->assertEquals($response, $response->setBody($body = '<html lang="en">Empty</html>'));
        $this->assertEquals($body, $response->getBody());
    }
    
    public function test_request_returns_json_response()
    {
        $this->server->append(
            new GuzzleResponse(
                $statusCode = 200,
                $headers = ['Content-Type' => 'application/json'],
                json_encode($body = ['foo' => $foo = 'bar'])
            )
        );
        
        $client = new Client([
            'handler' => $this->handler,
        ]);
        
        $response = $client->request('GET', '/test-url');
        
        $this->assertRequestSent('GET', '/test-url');
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(1.1, $response->getProtocolVersion());
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals($headers, $response->getHeaders());
        $this->assertEquals($response, $response->appendHeader('Server', $server = 'The Coders Zone'));
        $this->assertEquals($server, $response->getHeader('server'));
        $this->assertEquals($body, $response->getBody());
        $this->assertEquals($foo, $response->foo);
    }
    
    public function test_request_url_includes_base_url()
    {
        $this->server->append(
            new GuzzleResponse()
        );
        
        $client = new Client([
            'handler' => $this->handler,
            'base_url' => 'https://thecoderszone.com/base',
        ]);
        
        $response = $client->request('GET', '/test-url');
        
        $this->assertRequestSent('GET', '/base/test-url');
        $this->assertInstanceOf(Response::class, $response);
    }
    
    public function test_response_callback_is_called()
    {
        $this->server->append(
            new GuzzleResponse(200, [], $body = 'hi')
        );
        
        $client = new Client([
            'handler' => $this->handler,
            'callback' => function (Response $response) use ($body) {
                $this->assertInstanceOf(Response::class, $response);
                $this->assertEquals($body, $response->body);
            },
        ]);
        
        $client->request('GET', '/test-url');
    }
    
    public function test_magic_method_routes_request()
    {
        $this->server->append(
            new GuzzleResponse(),
            new GuzzleResponse(),
            new GuzzleResponse(),
            new GuzzleResponse(),
            new GuzzleResponse(),
            new GuzzleResponse()
        );
        
        $client = new Client([
            'handler' => $this->handler,
        ]);
        
        $response = $client->get('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('GET', '/url');
        $response = $client->head('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('HEAD', '/url');
        $response = $client->put('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('PUT', '/url');
        $response = $client->post('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('POST', '/url');
        $response = $client->patch('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('PATCH', '/url');
        $response = $client->delete('/url');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertRequestSent('DELETE', '/url');
    }
}
