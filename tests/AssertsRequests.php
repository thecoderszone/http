<?php
/**
 * Copyright Paul Adams, 2020. All rights reserved.
 * Unauthorized reproduction is prohibited.
 *
 * @package Dashboard
 * @author Paul Adams <paul@thecoderszone.com>
 */

namespace Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

trait AssertsRequests
{
    /**
     * @var HandlerStack
     */
    protected $handler;
    
    /**
     * @var MockHandler
     */
    protected $server;
    
    protected $history = [];
    
    public function createHandler()
    {
        $this->handler = HandlerStack::create($this->server = new MockHandler());
        $this->handler->push(Middleware::history($this->history));
    }
    
    public function assertRequestSent($method, $endpoint)
    {
        $this->assertGreaterThan(0, count($this->history));
        
        $request = reset($this->history)['request'];
        
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($endpoint, $request->getUri()->getPath());
        
        unset($this->history[key($this->history)]);
    }
}
