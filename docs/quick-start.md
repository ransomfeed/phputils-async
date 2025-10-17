# Quick Start Guide

Get up and running with **phputils-async** in 5 minutes! This guide will walk you through your first asynchronous HTTP requests.

## 🚀 Installation

Choose your preferred installation method:

### Option 1: Composer (Recommended)
```bash
composer require phputils/async
```

### Option 2: Standalone File
Download `phputils-async-standalone.php` and include it:
```php
require_once 'phputils-async-standalone.php';
```

### Option 3: Manual Files
Include the source files:
```php
require_once 'src/Phputils/Async/HttpClient.php';
require_once 'src/Phputils/Async/Request.php';
require_once 'src/Phputils/Async/Response.php';
```

## 📝 Your First Async Request

### Basic GET Requests

```php
<?php
// Include the library (choose your method)
require_once 'vendor/autoload.php'; // Composer
// OR require_once 'phputils-async-standalone.php'; // Standalone

use Phputils\Async\HttpClient;

// Create client
$client = new HttpClient();

// Make multiple GET requests
$urls = [
    'https://httpbin.org/get',
    'https://api.github.com',
    'https://httpbin.org/json'
];

$responses = $client->get($urls);

// Process responses
foreach ($responses as $url => $response) {
    echo "URL: $url\n";
    echo "Status: {$response['status']}\n";
    echo "Body length: " . strlen($response['body']) . " bytes\n\n";
}
?>
```

**Expected Output:**
```
URL: https://httpbin.org/get
Status: 200
Body length: 1234 bytes

URL: https://api.github.com
Status: 200
Body length: 5678 bytes

URL: https://httpbin.org/json
Status: 200
Body length: 234 bytes
```

### POST Requests

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient();

// POST requests with data
$requests = [
    ['url' => 'https://httpbin.org/post', 'body' => 'Hello World'],
    ['url' => 'https://httpbin.org/post', 'body' => '{"name": "John", "age": 30}']
];

$responses = $client->post($requests);

foreach ($responses as $url => $response) {
    echo "POST to $url: Status {$response['status']}\n";
}
?>
```

## ⚙️ Configuration Options

### Basic Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 10,           // Request timeout in seconds
    'concurrency' => 5,        // Max parallel requests
    'headers' => [             // Default headers
        'User-Agent: MyApp/1.0',
        'Accept: application/json'
    ]
]);

$responses = $client->get(['https://api.example.com']);
?>
```

### Per-Request Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient();

$responses = $client->get(['https://api.example.com'], [
    'timeout' => 5,                    // Override timeout
    'headers' => ['X-Custom: value'],  // Additional headers
    'concurrency' => 3                 // Limit concurrency
]);
?>
```

## 🎯 Using Request Objects

For more control, use Request objects:

```php
<?php
use Phputils\Async\HttpClient;
use Phputils\Async\Request;

$client = new HttpClient();

// Create requests with specific configurations
$requests = [
    Request::get('https://api.example.com/users')
        ->addHeader('Authorization', 'Bearer token123'),
    
    Request::post('https://api.example.com/users', '{"name": "John"}')
        ->addHeader('Content-Type', 'application/json'),
    
    Request::get('https://api.example.com/posts')
        ->setOption('timeout', 15)
];

$responses = $client->request('GET', $requests);
?>
```

## 🔄 Callback Functions

Process responses as they complete:

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient();

$processedCount = 0;

$callback = function ($url, $response) use (&$processedCount) {
    $processedCount++;
    echo "Processed $processedCount: $url - Status: {$response['status']}\n";
    
    if ($response['status'] === 200) {
        // Process successful response
        $data = json_decode($response['body'], true);
        echo "Data received: " . json_encode($data) . "\n";
    }
};

$urls = [
    'https://httpbin.org/get',
    'https://httpbin.org/json',
    'https://httpbin.org/user-agent'
];

$responses = $client->get($urls, ['callback' => $callback]);
?>
```

## 🚨 Error Handling

Handle errors gracefully:

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient();

$urls = [
    'https://httpbin.org/status/200',  // Success
    'https://httpbin.org/status/404',  // Not Found
    'https://invalid-domain.com'       // Network Error
];

$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    if (!empty($response['error'])) {
        echo "❌ Error for $url: {$response['error']}\n";
    } elseif ($response['status'] >= 400) {
        echo "⚠️ HTTP Error for $url: {$response['status']}\n";
    } else {
        echo "✅ Success for $url: {$response['status']}\n";
    }
}
?>
```

## 📊 Performance Example

Compare async vs sequential performance:

```php
<?php
use Phputils\Async\HttpClient;

$urls = [
    'https://httpbin.org/delay/1',
    'https://httpbin.org/delay/1',
    'https://httpbin.org/delay/1'
];

$client = new HttpClient(['concurrency' => 3]);

// Async execution
$startTime = microtime(true);
$asyncResponses = $client->get($urls);
$asyncTime = microtime(true) - $startTime;

// Sequential execution (simulate)
$sequentialClient = new HttpClient(['concurrency' => 1]);
$startTime = microtime(true);
$sequentialResponses = $sequentialClient->get($urls);
$sequentialTime = microtime(true) - $startTime;

echo "Async time: " . round($asyncTime, 2) . " seconds\n";
echo "Sequential time: " . round($sequentialTime, 2) . " seconds\n";
echo "Speed improvement: " . round($sequentialTime / $asyncTime, 2) . "x faster\n";
?>
```

## 🎉 Complete Example

Here's a complete, practical example:

```php
<?php
require_once 'vendor/autoload.php'; // or your chosen method

use Phputils\Async\HttpClient;
use Phputils\Async\Request;

// Initialize client with configuration
$client = new HttpClient([
    'timeout' => 10,
    'concurrency' => 5,
    'headers' => [
        'User-Agent: MyAwesomeApp/1.0',
        'Accept: application/json'
    ]
]);

// Check if async is available
echo "Async support: " . ($client->isAsyncAvailable() ? 'Yes' : 'No') . "\n\n";

// Example 1: Simple GET requests
echo "=== GET Requests ===\n";
$urls = [
    'https://httpbin.org/get',
    'https://api.github.com/zen',
    'https://httpbin.org/user-agent'
];

$responses = $client->get($urls);
foreach ($responses as $url => $response) {
    echo "✅ $url: {$response['status']} (" . strlen($response['body']) . " bytes)\n";
}

// Example 2: POST requests with data
echo "\n=== POST Requests ===\n";
$postRequests = [
    ['url' => 'https://httpbin.org/post', 'body' => 'Hello from phputils-async!'],
    ['url' => 'https://httpbin.org/post', 'body' => '{"message": "JSON data"}']
];

$postResponses = $client->post($postRequests);
foreach ($postResponses as $url => $response) {
    echo "✅ $url: {$response['status']}\n";
}

// Example 3: Using Request objects with custom headers
echo "\n=== Custom Request Objects ===\n";
$customRequests = [
    Request::get('https://httpbin.org/headers')
        ->addHeader('X-Custom-Header', 'MyValue'),
    Request::post('https://httpbin.org/post', 'Custom data')
        ->addHeader('Content-Type', 'text/plain')
];

$customResponses = $client->request('GET', $customRequests);
foreach ($customResponses as $url => $response) {
    echo "✅ $url: {$response['status']}\n";
}

echo "\n=== All requests completed! ===\n";
?>
```

## 🔗 Next Steps

Now that you're up and running:

1. **Explore Configuration**: Check out [Configuration Options](configuration.md)
2. **Learn the API**: Study the [API Reference](api-reference.md)
3. **See More Examples**: Browse [Examples & Use Cases](examples.md)
4. **Optimize Performance**: Read the [Performance Guide](performance.md)

## ❓ Need Help?

If you run into issues:

1. Check the [Troubleshooting Guide](troubleshooting.md)
2. Review the [API Reference](api-reference.md)
3. Open an [issue](https://github.com/ransomfeed/phputils-async/issues)

---

*Congratulations! You've successfully made your first asynchronous HTTP requests with phputils-async!*
