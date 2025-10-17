# phputils-async

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/ransomfeed/phputils-async)

A lightweight PHP library for asynchronous HTTP requests using native `curl_multi_*` functions. No external dependencies required!

## Features

- 🚀 **Asynchronous HTTP requests** using `curl_multi_*` functions
- 🔄 **Automatic fallback** to synchronous requests if async is not available
- ⚡ **Concurrency control** - limit the number of parallel requests
- 🎯 **Simple API** - clean and intuitive interface
- 📦 **Zero dependencies** - uses only PHP built-in functions
- 🔧 **Flexible configuration** - custom headers, timeouts, and options
- 📊 **Response objects** - structured response handling
- 🧪 **Well tested** - comprehensive test suite included

## Requirements

- PHP 7.4 or higher
- cURL extension enabled

## Installation

```bash
composer require phputility/async
```

Or add it to your `composer.json`:

```json
{
    "require": {
        "phputility/async": "^1.0"
    }
}
```

## Quick Start

### Basic GET Requests

```php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 5,
    'headers' => ['User-Agent: MyApp/1.0']
]);

$responses = $client->get([
    'https://api.github.com',
    'https://httpbin.org/get',
    'https://api.ransomfeed.it'
]);

foreach ($responses as $url => $response) {
    echo "[$url] Status: " . $response['status'] . "\n";
    echo "Body length: " . strlen($response['body']) . "\n\n";
}
```

### POST Requests

```php
$requests = [
    ['url' => 'https://httpbin.org/post', 'body' => 'Hello World'],
    ['url' => 'https://httpbin.org/post', 'body' => '{"key": "value"}']
];

$responses = $client->post($requests);

foreach ($responses as $url => $response) {
    if ($response['status'] === 200) {
        echo "Success: $url\n";
    }
}
```

### Using Request Objects

```php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com/data'),
    Request::post('https://api.example.com/create', '{"name": "test"}')
        ->addHeader('Content-Type', 'application/json'),
    Request::put('https://api.example.com/update/1', '{"status": "active"}')
];

$responses = $client->request('GET', $requests);
```

## Advanced Usage

### Concurrency Control

```php
$client = new HttpClient([
    'concurrency' => 5,  // Maximum 5 parallel requests
    'timeout' => 10
]);

$urls = array_fill(0, 20, 'https://httpbin.org/delay/1');
$responses = $client->get($urls);

// This will process 5 requests at a time, taking about 4 seconds total
```

### Custom Headers and Options

```php
$client = new HttpClient([
    'headers' => [
        'Authorization: Bearer token123',
        'X-API-Version: v2'
    ],
    'timeout' => 15
]);

// Override headers for specific requests
$responses = $client->get(['https://api.example.com'], [
    'headers' => ['X-Custom: value']
]);
```

### Callback Function

```php
$processedCount = 0;

$callback = function ($url, $response) use (&$processedCount) {
    $processedCount++;
    echo "Processed $processedCount: $url - Status: {$response['status']}\n";
    
    if ($response['status'] === 200) {
        // Process successful response
        $data = json_decode($response['body'], true);
        // ... handle data
    }
};

$responses = $client->get($urls, ['callback' => $callback]);
```

### Error Handling

```php
$urls = [
    'https://httpbin.org/status/200',
    'https://httpbin.org/status/404',
    'https://invalid-domain.com'
];

$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    if (!empty($response['error'])) {
        echo "Error for $url: {$response['error']}\n";
    } elseif ($response['status'] >= 400) {
        echo "HTTP Error for $url: {$response['status']}\n";
    } else {
        echo "Success for $url: {$response['status']}\n";
    }
}
```

## API Reference

### HttpClient

#### Constructor

```php
new HttpClient(array $options = [])
```

**Options:**
- `timeout` (int): Request timeout in seconds (default: 30)
- `headers` (array): Default headers for all requests
- `concurrency` (int): Maximum parallel requests (default: 10)
- `callback` (callable): Callback function for each completed request
- `user_agent` (string): User agent string (default: 'phputils-async/1.0')

#### Methods

##### `get(array $urls, array $options = []): array`
Execute GET requests asynchronously.

##### `post(array $requests, array $options = []): array`
Execute POST requests asynchronously.

##### `request(string $method, array $requests, array $options = []): array`
Execute requests with specified HTTP method.

##### `isAsyncAvailable(): bool`
Check if curl_multi_* functions are available.

### Request

#### Static Methods

```php
Request::get(string $url, array $headers = [], array $options = []): Request
Request::post(string $url, string $body = null, array $headers = [], array $options = []): Request
Request::put(string $url, string $body = null, array $headers = [], array $options = []): Request
Request::delete(string $url, string $body = null, array $headers = [], array $options = []): Request
```

#### Instance Methods

```php
$request->addHeader(string $name, string $value): Request
$request->setBody(string $body): Request
$request->setOption(string $key, mixed $value): Request
$request->toArray(): array
```

### Response

#### Properties

- `$status` (int|null): HTTP status code
- `$headers` (array): Response headers
- `$body` (string): Response body
- `$error` (string|null): Error message
- `$info` (array): Additional cURL info

#### Methods

```php
$response->isSuccess(): bool  // Check if status is 200-299
$response->toArray(): array   // Convert to array format
```

## Performance

### Benchmark Example

```php
$urls = array_fill(0, 10, 'https://httpbin.org/delay/1');

// Async execution
$start = microtime(true);
$asyncResponses = $client->get($urls);
$asyncTime = microtime(true) - $start;

// Sequential execution
$start = microtime(true);
$sequentialClient = new HttpClient(['concurrency' => 1]);
$sequentialResponses = $sequentialClient->get($urls);
$sequentialTime = microtime(true) - $start;

echo "Async time: " . round($asyncTime, 2) . "s\n";
echo "Sequential time: " . round($sequentialTime, 2) . "s\n";
echo "Speed improvement: " . round($sequentialTime / $asyncTime, 2) . "x\n";
```

**Typical results:**
- Async: ~1.2 seconds
- Sequential: ~10.1 seconds
- **Speed improvement: ~8.4x faster**

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

Run code quality checks:

```bash
composer quality
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

### 1.0.0
- Initial release
- Async HTTP requests with curl_multi_*
- Concurrency control
- Request/Response objects
- Comprehensive test suite
- Zero external dependencies

## Support

If you encounter any issues or have questions, please:

1. Check the [Issues](https://github.com/ransomfeed/phputils-async/issues) page
2. Create a new issue with detailed information
3. For questions, use the [Discussions](https://github.com/ransomfeed/phputils-async/discussions) page

---

Made with ❤️ by the Nuke{} (Ransomfeed team)
