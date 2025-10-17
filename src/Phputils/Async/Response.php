<?php

namespace Phputils\Async;

/**
 * HTTP Response class for phputils-async
 * 
 * Represents the result of an HTTP request with status, headers, body and error information.
 */
class Response
{
    /**
     * HTTP status code
     * 
     * @var int|null
     */
    public $status;

    /**
     * Response headers
     * 
     * @var array
     */
    public $headers;

    /**
     * Response body
     * 
     * @var string
     */
    public $body;

    /**
     * Error message if request failed
     * 
     * @var string|null
     */
    public $error;

    /**
     * Additional cURL info
     * 
     * @var array
     */
    public $info;

    /**
     * Constructor
     * 
     * @param int|null $status HTTP status code
     * @param array $headers Response headers
     * @param string $body Response body
     * @param string|null $error Error message
     * @param array $info Additional cURL info
     */
    public function __construct(?int $status = null, array $headers = [], string $body = '', ?string $error = null, array $info = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
        $this->error = $error;
        $this->info = $info;
    }

    /**
     * Check if the request was successful (status 200-299)
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status !== null && $this->status >= 200 && $this->status < 300;
    }

    /**
     * Get response as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'headers' => $this->headers,
            'body' => $this->body,
            'error' => $this->error,
            'info' => $this->info
        ];
    }

    /**
     * Convert to array for backward compatibility
     * 
     * @return array
     */
    public function __toArray(): array
    {
        return $this->toArray();
    }
}
