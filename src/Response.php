<?php
/**
 * Copyright Paul Adams, 2020. All rights reserved.
 * Unauthorized reproduction is prohibited.
 *
 * @package Dashboard
 * @author Paul Adams <paul@thecoderszone.com>
 */

namespace TCZ\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Response class.
 *
 * @package TCZ\Http
 */
class Response
{
    /**
     * The protocol version.
     *
     * @var string
     */
    public $version;
    
    /**
     * The status code.
     *
     * @var int
     */
    public $code;
    
    /**
     * The reason phrase.
     *
     * @var string
     */
    public $reason;
    
    /**
     * The response headers.
     *
     * @var array
     */
    public $headers;
    
    /**
     * The response body.
     *
     * @var mixed
     */
    public $body;
    
    /**
     * The header names.
     *
     * @var array
     */
    protected $headerNames;
    
    /**
     * Instantiate the response.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->version = (float) $response->getProtocolVersion();
        $this->code = $response->getStatusCode();
        $this->reason = $response->getReasonPhrase();
        $this->parseHeaders($response->getHeaders());
        $this->body = $this->parseBody(
            $response->getBody()->getContents()
        );
    }
    
    /**
     * Parse the response headers.
     *
     * @param array $headers
     */
    final protected function parseHeaders(array $headers)
    {
        foreach ($headers as $name => $values) {
            if (count($values) === 1) {
                $values = $values[0];
            }
            
            $this->setHeader($name, $values);
        }
    }
    
    /**
     * Parse the response body.
     *
     * @param string $body
     *
     * @return string
     */
    protected function parseBody($body)
    {
        return $body;
    }
    
    /**
     * Get the protocol version.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }
    
    /**
     * Set the protocol version.
     *
     * @param string $version
     *
     * @return Response
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;
        
        return $this;
    }
    
    /**
     * Get the status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->code;
    }
    
    /**
     * Get the reason phrase.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reason;
    }
    
    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->code < 400;
    }
    
    /**
     * Set the status.
     *
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return Response
     */
    public function setStatus($code, $reasonPhrase = null)
    {
        $this->code = $code;
        $this->reason = $reasonPhrase;
        
        return $this;
    }
    
    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Get a header.
     *
     * @param string $name
     *
     * @return string|array
     */
    public function getHeader($name)
    {
        $headerName = $this->getHeaderName($name);
        
        return $this->headers[$headerName] ?? null;
    }
    
    /**
     * Get a comma separated header string.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);
        
        if (is_array($header)) {
            return implode(', ', $header);
        }
        
        return $header;
    }
    
    /**
     * Determine if the response has a header.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return !is_null($this->getHeader($name));
    }
    
    /**
     * Set a header.
     *
     * @param string $name
     * @param string|array $value
     *
     * @return Response
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        $this->headerNames[strtolower($name)] = $name;
        
        return $this;
    }
    
    /**
     * Append a header.
     *
     * @param string $name
     * @param string|array $value
     *
     * @return Response
     */
    public function appendHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            return $this->setHeader($name, $value);
        }
        
        $name = $this->getHeaderName($name);
        
        $this->headers[$name] = array_merge((array) $this->headers[$name], (array) $value);
        
        return $this;
    }
    
    /**
     * Remove a header.
     *
     * @param string $name
     *
     * @return Response
     */
    public function removeHeader($name)
    {
        $name = $this->getHeaderName($name);
        
        unset($this->headers[$name]);
        
        return $this;
    }
    
    /**
     * Get a header name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getHeaderName($name)
    {
        return $this->headerNames[strtolower($name)] ?? $name;
    }
    
    /**
     * Get the body.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set the body.
     *
     * @param string $body
     *
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;
        
        return $this;
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
        return null;
    }
}
