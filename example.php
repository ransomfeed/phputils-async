<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phputils\Async\HttpClient;
use Phputils\Async\Request;

echo "=== phputils-async Example ===\n\n";

// Initialize client
$client = new HttpClient([
    'timeout' => 10,
    'headers' => ['User-Agent: phputils-async-example/1.0'],
    'concurrency' => 3
]);

echo "Async support available: " . ($client->isAsyncAvailable() ? 'Yes' : 'No') . "\n\n";

// Example 1: Basic GET requests
echo "1. Basic GET requests:\n";
$startTime = microtime(true);

$urls = [
    'https://httpbin.org/get',
    'https://httpbin.org/status/200',
    'https://httpbin.org/json'
];

$responses = $client->get($urls);
$endTime = microtime(true);

foreach ($responses as $url => $response) {
    $status = is_array($response) ? $response['status'] : $response->status;
    $bodyLength = is_array($response) ? strlen($response['body']) : strlen($response->body);
    
    echo "  - $url: Status $status, Body length: $bodyLength\n";
}

echo "  Time taken: " . round($endTime - $startTime, 2) . " seconds\n\n";

// Example 2: POST requests
echo "2. POST requests:\n";
$startTime = microtime(true);

$postRequests = [
    ['url' => 'https://httpbin.org/post', 'body' => 'Hello from phputils-async!'],
    ['url' => 'https://httpbin.org/post', 'body' => '{"message": "JSON data"}']
];

$postResponses = $client->post($postRequests);
$endTime = microtime(true);

foreach ($postResponses as $url => $response) {
    $status = is_array($response) ? $response['status'] : $response->status;
    echo "  - $url: Status $status\n";
}

echo "  Time taken: " . round($endTime - $startTime, 2) . " seconds\n\n";

// Example 3: Using Request objects
echo "3. Using Request objects:\n";

$requests = [
    Request::get('https://httpbin.org/get'),
    Request::post('https://httpbin.org/post', 'Test data')
        ->addHeader('Content-Type', 'text/plain'),
    Request::get('https://httpbin.org/user-agent')
];

$requestResponses = $client->request('GET', $requests);

foreach ($requestResponses as $url => $response) {
    $status = is_array($response) ? $response['status'] : $response->status;
    echo "  - $url: Status $status\n";
}

echo "\n";

// Example 4: Benchmark comparison
echo "4. Benchmark: Async vs Sequential:\n";

$testUrls = [
    'https://httpbin.org/delay/1',
    'https://httpbin.org/delay/1',
    'https://httpbin.org/delay/1'
];

// Async execution
$startTime = microtime(true);
$asyncResponses = $client->get($testUrls);
$asyncTime = microtime(true) - $startTime;

// Sequential execution
$sequentialClient = new HttpClient(['concurrency' => 1]);
$startTime = microtime(true);
$sequentialResponses = $sequentialClient->get($testUrls);
$sequentialTime = microtime(true) - $startTime;

echo "  Async time: " . round($asyncTime, 2) . " seconds\n";
echo "  Sequential time: " . round($sequentialTime, 2) . " seconds\n";
echo "  Speed improvement: " . round($sequentialTime / $asyncTime, 2) . "x faster\n\n";

// Example 5: Callback function
echo "5. Using callback function:\n";

$callbackCount = 0;
$callback = function ($url, $response) use (&$callbackCount) {
    $callbackCount++;
    $status = is_array($response) ? $response['status'] : $response->status;
    echo "  Callback #$callbackCount: $url -> Status $status\n";
};

$callbackResponses = $client->get($urls, ['callback' => $callback]);
echo "  Total responses processed: $callbackCount\n\n";

echo "=== Example completed ===\n";
