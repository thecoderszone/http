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
use Psr\Http\Message\StreamInterface;

/**
 * Response class.
 *
 * @package TCZ\Http
 */
class Response implements ResponseInterface
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
     * The body stream.
     *
     * @var StreamInterface
     */
    protected $stream;
    
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
        $this->stream = $response->getBody();
        $this->body = $this->parseBody(
            $this->stream->getContents()
        );
    }
    
    /**
     * Parse the response headers.
     *
     * @param array $headers
     */
    protected function parseHeaders(array $headers)
    {
        foreach ($headers as $name => $values) {
            if (count($values) === 1) {
                $values = $values[0];
            }
            
            $this->setHeader($name, $values);
        }
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
     * Get the headers.
     *
     * @return string[][]
     */
    public function getHeaders()
    {
        return array_map(function ($values) {
            if (!is_array($values)) {
                return [$values];
            }
            
            return $values;
        }, $this->headers);
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
        return implode(', ', $this->getHeader($name));
    }
    
    /**
     * Get a header.
     *
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        $headerName = $this->getHeaderName($name);
        
        $values = $this->headers[$headerName] ?? [];
        
        if (!is_array($values)) {
            return [$values];
        }
        
        return $values;
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
     * @return StreamInterface
     */
    public function getBody()
    {
        return $this->stream;
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
     * Set the protocol version.
     *
     * @param string $version
     *
     * @return Response
     */
    public function withProtocolVersion($version)
    {
        return $this->setProtocolVersion($version);
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
     * Set a header.
     *
     * @param string $name
     * @param string|array $value
     *
     * @return Response
     */
    public function withHeader($name, $value)
    {
        return $this->setHeader($name, $value);
    }
    
    /**
     * Append a header.
     *
     * @param string $name
     * @param string|array $value
     *
     * @return Response
     */
    public function withAddedHeader($name, $value)
    {
        return $this->appendHeader($name, $value);
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
     * Determine if the response has a header.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return count($this->getHeader($name)) > 0;
    }
    
    /**
     * Remove a header.
     *
     * @param string $name
     *
     * @return Response
     */
    public function withoutHeader($name)
    {
        return $this->removeHeader($name);
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
     * Set the body.
     *
     * @param StreamInterface $body
     *
     * @return Response
     */
    public function withBody(StreamInterface $body)
    {
        return $this->setBody($body->getContents());
    }
    
    /**
     * Set the status.
     *
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return Response
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->setStatus($code, $reasonPhrase);
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
