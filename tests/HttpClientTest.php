<?php

namespace Phputils\Async\Tests;

use Phputils\Async\HttpClient;
use Phputils\Async\Request;
use Phputils\Async\Response;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for HttpClient
 */
class HttpClientTest extends TestCase
{
    private HttpClient $client;

    protected function setUp(): void
    {
        $this->client = new HttpClient([
            'timeout' => 10,
            'headers' => ['User-Agent: phputils-async-test/1.0']
        ]);
    }

    public function testClientInitialization(): void
    {
        $client = new HttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testClientWithCustomOptions(): void
    {
        $options = [
            'timeout' => 15,
            'headers' => ['X-Custom: test'],
            'concurrency' => 5
        ];
        
        $client = new HttpClient($options);
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testGetRequests(): void
    {
        $urls = [
            'https://httpbin.org/get',
            'https://httpbin.org/status/200'
        ];

        $responses = $this->client->get($urls);

        $this->assertIsArray($responses);
        $this->assertCount(2, $responses);

        foreach ($urls as $url) {
            $this->assertArrayHasKey($url, $responses);
            $response = $responses[$url];
            
            if (is_array($response)) {
                $this->assertArrayHasKey('status', $response);
                $this->assertArrayHasKey('body', $response);
                $this->assertArrayHasKey('headers', $response);
            } else {
                $this->assertInstanceOf(Response::class, $response);
                $this->assertTrue($response->isSuccess());
            }
        }
    }

    public function testPostRequests(): void
    {
        $requests = [
            ['url' => 'https://httpbin.org/post', 'body' => 'test data'],
            ['url' => 'https://httpbin.org/post', 'body' => '{"key": "value"}']
        ];

        $responses = $this->client->post($requests);

        $this->assertIsArray($responses);
        $this->assertCount(2, $responses);

        foreach ($responses as $url => $response) {
            $this->assertContains($url, array_column($requests, 'url'));
            
            if (is_array($response)) {
                $this->assertArrayHasKey('status', $response);
                $this->assertArrayHasKey('body', $response);
            } else {
                $this->assertInstanceOf(Response::class, $response);
                $this->assertTrue($response->isSuccess());
            }
        }
    }

    public function testRequestWithCustomOptions(): void
    {
        $requests = [
            new Request('GET', 'https://httpbin.org/get')
        ];

        $options = [
            'timeout' => 5,
            'headers' => ['X-Test: custom-value']
        ];

        $responses = $this->client->request('GET', $requests, $options);

        $this->assertIsArray($responses);
        $this->assertCount(1, $responses);
    }

    public function testAsyncAvailability(): void
    {
        $isAvailable = $this->client->isAsyncAvailable();
        $this->assertIsBool($isAvailable);
    }

    public function testRequestClass(): void
    {
        $request = new Request('GET', 'https://example.com');
        $this->assertEquals('GET', $request->method);
        $this->assertEquals('https://example.com', $request->url);

        $postRequest = Request::post('https://example.com', 'data');
        $this->assertEquals('POST', $postRequest->method);
        $this->assertEquals('data', $postRequest->body);

        $request->addHeader('Content-Type', 'application/json');
        $this->assertEquals(['Content-Type' => 'application/json'], $request->headers);
    }

    public function testResponseClass(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"success": true}');
        
        $this->assertEquals(200, $response->status);
        $this->assertEquals(['Content-Type' => 'application/json'], $response->headers);
        $this->assertEquals('{"success": true}', $response->body);
        $this->assertTrue($response->isSuccess());

        $errorResponse = new Response(404, [], '', 'Not found');
        $this->assertFalse($errorResponse->isSuccess());
        $this->assertEquals('Not found', $errorResponse->error);

        $array = $response->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('body', $array);
    }

    public function testConcurrencyLimit(): void
    {
        $urls = array_fill(0, 20, 'https://httpbin.org/delay/1');
        
        $startTime = microtime(true);
        $responses = $this->client->get($urls, ['concurrency' => 3]);
        $endTime = microtime(true);
        
        $this->assertCount(20, $responses);
        
        // With concurrency limit of 3, it should take at least 7 seconds (20 requests / 3 concurrent = ~7 batches)
        $executionTime = $endTime - $startTime;
        $this->assertGreaterThan(6, $executionTime);
    }

    public function testCallbackFunction(): void
    {
        $callbacks = [];
        
        $callback = function ($url, $response) use (&$callbacks) {
            $callbacks[] = ['url' => $url, 'response' => $response];
        };

        $urls = ['https://httpbin.org/get', 'https://httpbin.org/status/200'];
        $this->client->get($urls, ['callback' => $callback]);

        $this->assertCount(2, $callbacks);
        
        foreach ($callbacks as $callbackResult) {
            $this->assertArrayHasKey('url', $callbackResult);
            $this->assertArrayHasKey('response', $callbackResult);
            $this->assertContains($callbackResult['url'], $urls);
        }
    }

    public function testBenchmarkVsSequential(): void
    {
        $urls = [
            'https://httpbin.org/delay/1',
            'https://httpbin.org/delay/1',
            'https://httpbin.org/delay/1'
        ];

        // Test async execution
        $startTime = microtime(true);
        $asyncResponses = $this->client->get($urls);
        $asyncTime = microtime(true) - $startTime;

        // Test sequential execution (simulate)
        $startTime = microtime(true);
        $sequentialClient = new HttpClient(['concurrency' => 1]);
        $sequentialResponses = $sequentialClient->get($urls);
        $sequentialTime = microtime(true) - $startTime;

        $this->assertCount(3, $asyncResponses);
        $this->assertCount(3, $sequentialResponses);

        // Async should be faster than sequential for multiple requests
        if ($this->client->isAsyncAvailable()) {
            $this->assertLessThan($sequentialTime, $asyncTime, 'Async execution should be faster than sequential');
        }
    }

    public function testErrorHandling(): void
    {
        $urls = [
            'https://httpbin.org/status/404',
            'https://httpbin.org/status/500',
            'https://nonexistent-domain-12345.com'
        ];

        $responses = $this->client->get($urls);

        $this->assertCount(3, $responses);

        foreach ($responses as $url => $response) {
            if (is_array($response)) {
                if (strpos($url, 'nonexistent') !== false) {
                    $this->assertNotEmpty($response['error']);
                } else {
                    $this->assertArrayHasKey('status', $response);
                    $this->assertContains($response['status'], [404, 500]);
                }
            } else {
                if (strpos($url, 'nonexistent') !== false) {
                    $this->assertNotEmpty($response->error);
                } else {
                    $this->assertFalse($response->isSuccess());
                }
            }
        }
    }

    protected function tearDown(): void
    {
        // Clean up if needed
    }
}
