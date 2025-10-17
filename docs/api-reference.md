# API Reference

Complete API documentation for **phputils-async** including all classes, methods, parameters, and return values.

## 📚 Class Overview

- **[HttpClient](#httpclient)** - Main class for asynchronous HTTP requests
- **[Request](#request)** - HTTP request representation
- **[Response](#response)** - HTTP response representation

## 🚀 HttpClient

The main class for performing asynchronous HTTP requests.

### Constructor

```php
public function __construct(array $options = [])
```

**Parameters:**
- `$options` (array): Configuration options (see [Configuration Guide](configuration.md))

**Example:**
```php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30,
    'concurrency' => 10,
    'headers' => ['User-Agent: MyApp/1.0']
]);
```

### Methods

#### get()

Execute GET requests asynchronously.

```php
public function get(array $urls, array $options = []): array
```

**Parameters:**
- `$urls` (array): Array of URLs to request
- `$options` (array): Optional request-specific options

**Returns:**
- `array`: Associative array with URLs as keys and responses as values

**Example:**
```php
$urls = [
    'https://api.github.com',
    'https://httpbin.org/get'
];

$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    echo "$url: {$response['status']}\n";
}
```

#### post()

Execute POST requests asynchronously.

```php
public function post(array $requests, array $options = []): array
```

**Parameters:**
- `$requests` (array): Array of request data or Request objects
- `$options` (array): Optional request-specific options

**Returns:**
- `array`: Associative array with URLs as keys and responses as values

**Request Format:**
```php
// Array format
$requests = [
    ['url' => 'https://api.example.com', 'body' => 'data'],
    ['url' => 'https://api.example.com', 'body' => '{"key": "value"}']
];

// Request object format
$requests = [
    Request::post('https://api.example.com', 'data'),
    Request::post('https://api.example.com', '{"key": "value"}')
];
```

**Example:**
```php
$requests = [
    ['url' => 'https://httpbin.org/post', 'body' => 'Hello World'],
    ['url' => 'https://httpbin.org/post', 'body' => '{"name": "John"}']
];

$responses = $client->post($requests);
```

#### request()

Execute HTTP requests with specified method.

```php
public function request(string $method, array $requests, array $options = []): array
```

**Parameters:**
- `$method` (string): HTTP method (GET, POST, PUT, DELETE, etc.)
- `$requests` (array): Array of URLs, arrays, or Request objects
- `$options` (array): Optional request-specific options

**Returns:**
- `array`: Associative array with URLs as keys and responses as values

**Request Formats:**
```php
// URL strings
$requests = ['https://api.example.com', 'https://api.github.com'];

// Array format
$requests = [
    ['url' => 'https://api.example.com', 'body' => 'data'],
    ['url' => 'https://api.github.com', 'headers' => ['Authorization: token']]
];

// Request objects
$requests = [
    Request::get('https://api.example.com'),
    Request::post('https://api.github.com', 'data')
];
```

**Example:**
```php
$requests = [
    Request::get('https://api.example.com/users'),
    Request::post('https://api.example.com/users', '{"name": "John"}'),
    Request::put('https://api.example.com/users/1', '{"name": "Jane"}')
];

$responses = $client->request('GET', $requests);
```

#### isAsyncAvailable()

Check if curl_multi_* functions are available.

```php
public function isAsyncAvailable(): bool
```

**Returns:**
- `bool`: True if async functionality is available, false otherwise

**Example:**
```php
if ($client->isAsyncAvailable()) {
    echo "Async requests supported\n";
} else {
    echo "Falling back to synchronous requests\n";
}
```

## 📝 Request

Represents an HTTP request with method, URL, headers, body, and options.

### Constructor

```php
public function __construct(string $method = 'GET', string $url = '', array $headers = [], ?string $body = null, array $options = [])
```

**Parameters:**
- `$method` (string): HTTP method (default: 'GET')
- `$url` (string): Request URL
- `$headers` (array): Request headers
- `$body` (string|null): Request body
- `$options` (array): Additional options

**Example:**
```php
$request = new Request('POST', 'https://api.example.com', ['Content-Type: application/json'], '{"key": "value"}');
```

### Static Factory Methods

#### get()

Create a GET request.

```php
public static function get(string $url, array $headers = [], array $options = []): self
```

**Parameters:**
- `$url` (string): Request URL
- `$headers` (array): Request headers
- `$options` (array): Additional options

**Returns:**
- `self`: Request instance

**Example:**
```php
$request = Request::get('https://api.example.com', ['Authorization: Bearer token']);
```

#### post()

Create a POST request.

```php
public static function post(string $url, ?string $body = null, array $headers = [], array $options = []): self
```

**Parameters:**
- `$url` (string): Request URL
- `$body` (string|null): Request body
- `$headers` (array): Request headers
- `$options` (array): Additional options

**Returns:**
- `self`: Request instance

**Example:**
```php
$request = Request::post('https://api.example.com', '{"name": "John"}', ['Content-Type: application/json']);
```

#### put()

Create a PUT request.

```php
public static function put(string $url, ?string $body = null, array $headers = [], array $options = []): self
```

**Parameters:**
- `$url` (string): Request URL
- `$body` (string|null): Request body
- `$headers` (array): Request headers
- `$options` (array): Additional options

**Returns:**
- `self`: Request instance

**Example:**
```php
$request = Request::put('https://api.example.com/users/1', '{"name": "Jane"}');
```

#### delete()

Create a DELETE request.

```php
public static function delete(string $url, ?string $body = null, array $headers = [], array $options = []): self
```

**Parameters:**
- `$url` (string): Request URL
- `$body` (string|null): Request body
- `$headers` (array): Request headers
- `$options` (array): Additional options

**Returns:**
- `self`: Request instance

**Example:**
```php
$request = Request::delete('https://api.example.com/users/1');
```

### Instance Methods

#### addHeader()

Add a header to the request.

```php
public function addHeader(string $name, string $value): self
```

**Parameters:**
- `$name` (string): Header name
- `$value` (string): Header value

**Returns:**
- `self`: Request instance (for method chaining)

**Example:**
```php
$request = Request::get('https://api.example.com')
    ->addHeader('Authorization', 'Bearer token123')
    ->addHeader('Content-Type', 'application/json');
```

#### setBody()

Set the request body.

```php
public function setBody(string $body): self
```

**Parameters:**
- `$body` (string): Request body

**Returns:**
- `self`: Request instance (for method chaining)

**Example:**
```php
$request = Request::post('https://api.example.com')
    ->setBody('{"name": "John", "age": 30}');
```

#### setOption()

Set a custom cURL option.

```php
public function setOption(string $key, $value): self
```

**Parameters:**
- `$key` (string): Option key (without CURLOPT_ prefix)
- `$value` (mixed): Option value

**Returns:**
- `self`: Request instance (for method chaining)

**Example:**
```php
$request = Request::get('https://api.example.com')
    ->setOption('timeout', 60)
    ->setOption('followlocation', true);
```

#### toArray()

Convert request to array format.

```php
public function toArray(): array
```

**Returns:**
- `array`: Request data as associative array

**Example:**
```php
$request = Request::post('https://api.example.com', 'data');
$array = $request->toArray();
// Returns: ['method' => 'POST', 'url' => 'https://api.example.com', 'body' => 'data', ...]
```

### Properties

- `$method` (string): HTTP method
- `$url` (string): Request URL
- `$headers` (array): Request headers
- `$body` (string|null): Request body
- `$options` (array): Additional options

## 📨 Response

Represents an HTTP response with status, headers, body, and metadata.

### Constructor

```php
public function __construct(?int $status = null, array $headers = [], string $body = '', ?string $error = null, array $info = [])
```

**Parameters:**
- `$status` (int|null): HTTP status code
- `$headers` (array): Response headers
- `$body` (string): Response body
- `$error` (string|null): Error message
- `$info` (array): Additional cURL info

**Example:**
```php
$response = new Response(200, ['Content-Type' => 'application/json'], '{"success": true}');
```

### Methods

#### isSuccess()

Check if the request was successful.

```php
public function isSuccess(): bool
```

**Returns:**
- `bool`: True if status is 200-299, false otherwise

**Example:**
```php
if ($response->isSuccess()) {
    echo "Request successful\n";
} else {
    echo "Request failed\n";
}
```

#### toArray()

Convert response to array format.

```php
public function toArray(): array
```

**Returns:**
- `array`: Response data as associative array

**Example:**
```php
$array = $response->toArray();
// Returns: ['status' => 200, 'headers' => [...], 'body' => '...', 'error' => null, 'info' => [...]]
```

### Properties

- `$status` (int|null): HTTP status code
- `$headers` (array): Response headers
- `$body` (string): Response body
- `$error` (string|null): Error message
- `$info` (array): Additional cURL info

## 🔧 Configuration Options

### HttpClient Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timeout` | `int` | `30` | Request timeout in seconds |
| `concurrency` | `int` | `10` | Maximum parallel requests |
| `headers` | `array` | `[]` | Default headers for all requests |
| `callback` | `callable\|null` | `null` | Callback function for completed requests |
| `user_agent` | `string` | `'phputils-async/1.0'` | User agent string |
| `follow_redirects` | `bool` | `true` | Follow HTTP redirects |
| `max_redirects` | `int` | `5` | Maximum number of redirects |
| `verify_ssl` | `bool` | `true` | Verify SSL certificates |
| `verify_host` | `int` | `2` | SSL host verification level |

### cURL Options

You can set custom cURL options using the `setOption()` method on Request objects:

```php
$request = Request::get('https://api.example.com')
    ->setOption('CONNECTTIMEOUT', 10)      // Connection timeout
    ->setOption('LOW_SPEED_TIME', 30)      // Low speed timeout
    ->setOption('LOW_SPEED_LIMIT', 1024)   // Low speed limit
    ->setOption('HTTP_VERSION', CURL_HTTP_VERSION_2_0)  // HTTP/2
    ->setOption('PROXY', 'http://proxy:8080')           // Proxy
    ->setOption('PROXYUSERPWD', 'user:pass');           // Proxy auth
```

## 📊 Response Format

### Array Format

When responses are returned as arrays (default behavior):

```php
[
    'status' => 200,
    'headers' => [
        'Content-Type' => 'application/json',
        'Content-Length' => '1234'
    ],
    'body' => '{"success": true}',
    'error' => null,
    'info' => [
        'total_time' => 1.234,
        'connect_time' => 0.123,
        'http_code' => 200
    ]
]
```

### Object Format

When using Response objects:

```php
$response = $responses['https://api.example.com'];
echo $response->status;    // 200
echo $response->body;      // Response body
echo $response->error;     // Error message (if any)
```

## 🚨 Error Handling

### Response Errors

```php
$responses = $client->get(['https://api.example.com']);

foreach ($responses as $url => $response) {
    if (!empty($response['error'])) {
        // cURL error
        echo "Error for $url: {$response['error']}\n";
    } elseif ($response['status'] >= 400) {
        // HTTP error
        echo "HTTP error for $url: {$response['status']}\n";
    } else {
        // Success
        echo "Success for $url: {$response['status']}\n";
    }
}
```

### Exception Handling

```php
try {
    $responses = $client->get(['https://api.example.com']);
} catch (Exception $e) {
    echo "Request failed: " . $e->getMessage() . "\n";
}
```

## 🔄 Callback Function

### Callback Signature

```php
function callback(string $url, array $response): void
```

**Parameters:**
- `$url` (string): The URL that was requested
- `$response` (array): The response data

**Example:**
```php
$callback = function ($url, $response) {
    echo "Completed: $url - Status: {$response['status']}\n";
    
    if ($response['status'] === 200) {
        $data = json_decode($response['body'], true);
        // Process successful response
    }
};

$client = new HttpClient(['callback' => $callback]);
$responses = $client->get(['https://api.example.com']);
```

## 📝 Type Hints

### Method Signatures

```php
// HttpClient
public function __construct(array $options = []): void
public function get(array $urls, array $options = []): array
public function post(array $requests, array $options = []): array
public function request(string $method, array $requests, array $options = []): array
public function isAsyncAvailable(): bool

// Request
public function __construct(string $method = 'GET', string $url = '', array $headers = [], ?string $body = null, array $options = []): void
public static function get(string $url, array $headers = [], array $options = []): self
public static function post(string $url, ?string $body = null, array $headers = [], array $options = []): self
public static function put(string $url, ?string $body = null, array $headers = [], array $options = []): self
public static function delete(string $url, ?string $body = null, array $headers = [], array $options = []): self
public function addHeader(string $name, string $value): self
public function setBody(string $body): self
public function setOption(string $key, $value): self
public function toArray(): array

// Response
public function __construct(?int $status = null, array $headers = [], string $body = '', ?string $error = null, array $info = []): void
public function isSuccess(): bool
public function toArray(): array
```

## 🔗 Related Documentation

- [Configuration Guide](configuration.md) - Detailed configuration options
- [Examples & Use Cases](examples.md) - Practical examples
- [Performance Guide](performance.md) - Optimization and benchmarking
- [Troubleshooting Guide](troubleshooting.md) - Common issues and solutions

---

*For more practical examples, see the [Examples & Use Cases](examples.md) guide*
