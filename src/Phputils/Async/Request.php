<?php

namespace Phputils\Async;

/**
 * HTTP Request class for phputils-async
 * 
 * Represents an HTTP request with method, URL, headers, body and options.
 */
class Request
{
    /**
     * HTTP method
     * 
     * @var string
     */
    public $method;

    /**
     * Request URL
     * 
     * @var string
     */
    public $url;

    /**
     * Request headers
     * 
     * @var array
     */
    public $headers;

    /**
     * Request body
     * 
     * @var string|null
     */
    public $body;

    /**
     * Additional options
     * 
     * @var array
     */
    public $options;

    /**
     * Constructor
     * 
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $headers Request headers
     * @param string|null $body Request body
     * @param array $options Additional options
     */
    public function __construct(string $method = 'GET', string $url = '', array $headers = [], ?string $body = null, array $options = [])
    {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->options = $options;
    }

    /**
     * Create a GET request
     * 
     * @param string $url Request URL
     * @param array $headers Request headers
     * @param array $options Additional options
     * @return self
     */
    public static function get(string $url, array $headers = [], array $options = []): self
    {
        return new self('GET', $url, $headers, null, $options);
    }

    /**
     * Create a POST request
     * 
     * @param string $url Request URL
     * @param string|null $body Request body
     * @param array $headers Request headers
     * @param array $options Additional options
     * @return self
     */
    public static function post(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('POST', $url, $headers, $body, $options);
    }

    /**
     * Create a PUT request
     * 
     * @param string $url Request URL
     * @param string|null $body Request body
     * @param array $headers Request headers
     * @param array $options Additional options
     * @return self
     */
    public static function put(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('PUT', $url, $headers, $body, $options);
    }

    /**
     * Create a DELETE request
     * 
     * @param string $url Request URL
     * @param string|null $body Request body
     * @param array $headers Request headers
     * @param array $options Additional options
     * @return self
     */
    public static function delete(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('DELETE', $url, $headers, $body, $options);
    }

    /**
     * Add header to request
     * 
     * @param string $name Header name
     * @param string $value Header value
     * @return self
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set request body
     * 
     * @param string $body Request body
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set request option
     * 
     * @param string $key Option key
     * @param mixed $value Option value
     * @return self
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Get request as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'url' => $this->url,
            'headers' => $this->headers,
            'body' => $this->body,
            'options' => $this->options
        ];
    }
}
