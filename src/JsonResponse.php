<?php
/**
 * Copyright Paul Adams, 2020. All rights reserved.
 * Unauthorized reproduction is prohibited.
 *
 * @package Dashboard
 * @author Paul Adams <paul@thecoderszone.com>
 */

namespace TCZ\Http;

/**
 * JSON response class.
 *
 * @package TCZ\Http
 */
class JsonResponse extends Response
{
    /**
     * The response body.
     *
     * @var array
     */
    public $body;
    
    /**
     * Parse the response.
     *
     * @param string $body
     *
     * @return array
     */
    protected function parseBody($body)
    {
        return json_decode($body, true);
    }
    
    /**
     * Get a value from the response body.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->body[$key] ?? null;
    }
}
