<?php

namespace Phputils\Async;

/**
 * Async HTTP Client for phputils-async
 * 
 * Provides asynchronous HTTP requests using curl_multi_* functions with fallback to synchronous requests.
 */
class HttpClient
{
    /**
     * Default options
     * 
     * @var array
     */
    private $defaultOptions;

    /**
     * Whether curl_multi_* functions are available
     * 
     * @var bool
     */
    private $curlMultiAvailable;

    /**
     * Constructor
     * 
     * @param array $options Default options for all requests
     */
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

    /**
     * Execute GET requests asynchronously
     * 
     * @param array $urls Array of URLs to request
     * @param array $options Additional options
     * @return array Array of responses keyed by URL
     */
    public function get(array $urls, array $options = []): array
    {
        $requests = [];
        foreach ($urls as $url) {
            $requests[] = Request::get($url);
        }
        
        return $this->request('GET', $requests, $options);
    }

    /**
     * Execute POST requests asynchronously
     * 
     * @param array $requests Array of Request objects or arrays with 'url' and 'body' keys
     * @param array $options Additional options
     * @return array Array of responses keyed by URL
     */
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

    /**
     * Execute HTTP requests asynchronously
     * 
     * @param string $method HTTP method
     * @param array $requests Array of Request objects or URLs (for GET requests)
     * @param array $options Additional options
     * @return array Array of responses keyed by URL
     */
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

    /**
     * Execute requests asynchronously using curl_multi_*
     * 
     * @param array $requests Array of Request objects
     * @param array $options Request options
     * @return array Array of responses keyed by URL
     */
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

    /**
     * Execute requests synchronously
     * 
     * @param array $requests Array of Request objects
     * @param array $options Request options
     * @return array Array of responses keyed by URL
     */
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

    /**
     * Create a cURL handle for a request
     * 
     * @param Request $request Request object
     * @param array $options Request options
     * @return resource cURL handle
     */
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

    /**
     * Create a Response object from a cURL handle
     * 
     * @param resource $handle cURL handle
     * @param Request $request Original request
     * @return Response Response object
     */
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

    /**
     * Check if curl_multi_* functions are available
     * 
     * @return bool
     */
    public function isAsyncAvailable(): bool
    {
        return $this->curlMultiAvailable;
    }
}
