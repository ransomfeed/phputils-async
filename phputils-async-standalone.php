<?php
/**
 * phputils-async - Standalone Version
 * 
 * This is a single-file version of phputils-async that includes all classes.
 * Perfect for hosting environments where Composer is not available.
 * 
 * Usage:
 * require_once 'phputils-async-standalone.php';
 * $client = new Phputils\Async\HttpClient();
 */

namespace Phputils\Async;

/**
 * HTTP Response class for phputils-async
 */
class Response
{
    public $status;
    public $headers;
    public $body;
    public $error;
    public $info;

    public function __construct(?int $status = null, array $headers = [], string $body = '', ?string $error = null, array $info = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
        $this->error = $error;
        $this->info = $info;
    }

    public function isSuccess(): bool
    {
        return $this->status !== null && $this->status >= 200 && $this->status < 300;
    }

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
}

/**
 * HTTP Request class for phputils-async
 */
class Request
{
    public $method;
    public $url;
    public $headers;
    public $body;
    public $options;

    public function __construct(string $method = 'GET', string $url = '', array $headers = [], ?string $body = null, array $options = [])
    {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->options = $options;
    }

    public static function get(string $url, array $headers = [], array $options = []): self
    {
        return new self('GET', $url, $headers, null, $options);
    }

    public static function post(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('POST', $url, $headers, $body, $options);
    }

    public static function put(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('PUT', $url, $headers, $body, $options);
    }

    public static function delete(string $url, ?string $body = null, array $headers = [], array $options = []): self
    {
        return new self('DELETE', $url, $headers, $body, $options);
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

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

/**
 * Async HTTP Client for phputils-async
 */
class HttpClient
{
    private $defaultOptions;
    private $curlMultiAvailable;

    public function __construct(array $options = [])
    {
        $this->defaultOptions = array_merge([
            'timeout' => 30,
            'headers' => [],
            'concurrency' => 10,
            'callback' => null,
            'user_agent' => 'phputils-async/1.0'
        ], $options);

        $this->curlMultiAvailable = function_exists('curl_multi_init') && function_exists('curl_multi_exec');
    }

    public function get(array $urls, array $options = []): array
    {
        $requests = [];
        foreach ($urls as $url) {
            $requests[] = Request::get($url);
        }
        
        return $this->request('GET', $requests, $options);
    }

    public function post(array $requests, array $options = []): array
    {
        $normalizedRequests = [];
        foreach ($requests as $request) {
            if (is_array($request)) {
                $normalizedRequests[] = Request::post($request['url'], $request['body'] ?? null);
            } elseif ($request instanceof Request) {
                $normalizedRequests[] = $request;
            }
        }
        
        return $this->request('POST', $normalizedRequests, $options);
    }

    public function request(string $method, array $requests, array $options = []): array
    {
        $mergedOptions = array_merge($this->defaultOptions, $options);
        
        // Normalize requests
        $normalizedRequests = [];
        foreach ($requests as $request) {
            if (is_string($request)) {
                $normalizedRequests[] = new Request($method, $request);
            } elseif (is_array($request)) {
                $normalizedRequests[] = new Request($method, $request['url'], $request['headers'] ?? [], $request['body'] ?? null, $request['options'] ?? []);
            } elseif ($request instanceof Request) {
                $normalizedRequests[] = $request;
            }
        }

        if ($this->curlMultiAvailable && count($normalizedRequests) > 1) {
            return $this->executeAsync($normalizedRequests, $mergedOptions);
        } else {
            return $this->executeSync($normalizedRequests, $mergedOptions);
        }
    }

    private function executeAsync(array $requests, array $options): array
    {
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $responses = [];
        $concurrency = $options['concurrency'] ?? 10;
        
        // Initialize all curl handles
        foreach ($requests as $index => $request) {
            $curlHandles[$index] = $this->createCurlHandle($request, $options);
            curl_multi_add_handle($multiHandle, $curlHandles[$index]);
        }

        // Execute requests with concurrency limit
        $running = 0;
        $completed = 0;
        $total = count($requests);
        
        do {
            // Start new requests if under concurrency limit
            while ($running < $concurrency && $completed + $running < $total) {
                $nextIndex = $completed + $running;
                if ($nextIndex < $total) {
                    curl_multi_add_handle($multiHandle, $curlHandles[$nextIndex]);
                    $running++;
                } else {
                    break;
                }
            }

            // Execute active handles
            curl_multi_exec($multiHandle, $active);
            curl_multi_select($multiHandle);

            // Process completed requests
            while (($info = curl_multi_info_read($multiHandle)) !== false) {
                if ($info['msg'] === CURLMSG_DONE) {
                    $handle = $info['handle'];
                    $index = array_search($handle, $curlHandles, true);
                    
                    if ($index !== false) {
                        $response = $this->createResponse($handle, $requests[$index]);
                        $responses[$requests[$index]->url] = $response;
                        
                        curl_multi_remove_handle($multiHandle, $handle);
                        curl_close($handle);
                        
                        $running--;
                        $completed++;
                        
                        // Call callback if provided
                        if ($options['callback'] && is_callable($options['callback'])) {
                            call_user_func($options['callback'], $requests[$index]->url, $response);
                        }
                    }
                }
            }
        } while ($active > 0 || $completed < $total);

        curl_multi_close($multiHandle);
        
        return $responses;
    }

    private function executeSync(array $requests, array $options): array
    {
        $responses = [];
        
        foreach ($requests as $request) {
            $handle = $this->createCurlHandle($request, $options);
            $response = $this->createResponse($handle, $request);
            $responses[$request->url] = $response;
            
            curl_close($handle);
            
            // Call callback if provided
            if ($options['callback'] && is_callable($options['callback'])) {
                call_user_func($options['callback'], $request->url, $response);
            }
        }
        
        return $responses;
    }

    private function createCurlHandle(Request $request, array $options)
    {
        $handle = curl_init();
        
        // Basic options
        curl_setopt_array($handle, [
            CURLOPT_URL => $request->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $options['timeout'] ?? 30,
            CURLOPT_USERAGENT => $options['user_agent'] ?? 'phputils-async/1.0',
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        // HTTP method
        switch (strtoupper($request->method)) {
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PATCH':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
        }

        // Request body
        if ($request->body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $request->body);
        }

        // Headers
        $headers = array_merge($options['headers'] ?? [], $request->headers);
        if (!empty($headers)) {
            $headerArray = [];
            foreach ($headers as $key => $value) {
                if (is_numeric($key)) {
                    $headerArray[] = $value;
                } else {
                    $headerArray[] = "$key: $value";
                }
            }
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headerArray);
        }

        // Custom options
        $customOptions = array_merge($options, $request->options);
        foreach ($customOptions as $key => $value) {
            if (defined("CURLOPT_$key")) {
                curl_setopt($handle, constant("CURLOPT_$key"), $value);
            }
        }

        return $handle;
    }

    private function createResponse($handle, Request $request): Response
    {
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        $info = curl_getinfo($handle);

        if ($response === false || !empty($error)) {
            return new Response(null, [], '', $error ?: 'cURL error', $info);
        }

        // Parse headers and body
        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $headersString = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Parse headers
        $headers = [];
        $headerLines = explode("\r\n", $headersString);
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                list($name, $value) = explode(':', $line, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        return new Response($httpCode, $headers, $body, null, $info);
    }

    public function isAsyncAvailable(): bool
    {
        return $this->curlMultiAvailable;
    }
}
