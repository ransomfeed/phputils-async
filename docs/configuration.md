# Configuration Options

This guide covers all available configuration options for **phputils-async**, from basic settings to advanced customization.

## 📋 Configuration Overview

phputils-async offers flexible configuration at multiple levels:

- **Global Configuration**: Set once in the constructor
- **Per-Request Configuration**: Override for specific requests
- **Request-Level Configuration**: Individual request settings

## 🔧 HttpClient Constructor Options

### Basic Options

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30,              // Request timeout in seconds
    'concurrency' => 10,          // Maximum parallel requests
    'headers' => [],              // Default headers for all requests
    'callback' => null,           // Callback function for completed requests
    'user_agent' => 'phputils-async/1.0'  // User agent string
]);
?>
```

### Complete Options Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `timeout` | `int` | `30` | Request timeout in seconds |
| `concurrency` | `int` | `10` | Maximum parallel requests |
| `headers` | `array` | `[]` | Default headers for all requests |
| `callback` | `callable\|null` | `null` | Callback function for completed requests |
| `user_agent` | `string` | `'phputils-async/1.0'` | User agent string |
| `follow_redirects` | `bool` | `true` | Follow HTTP redirects |
| `max_redirects` | `int` | `5` | Maximum number of redirects to follow |
| `verify_ssl` | `bool` | `true` | Verify SSL certificates |
| `verify_host` | `int` | `2` | SSL host verification level |

## 🎯 Per-Request Configuration

Override global settings for specific requests:

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30,
    'headers' => ['User-Agent: MyApp/1.0']
]);

// Override configuration for this request
$responses = $client->get(['https://api.example.com'], [
    'timeout' => 60,                    // Override timeout
    'headers' => ['X-Custom: value'],   // Additional headers
    'concurrency' => 5                  // Limit concurrency
]);
?>
```

## 📡 Header Configuration

### Global Headers

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'headers' => [
        'User-Agent: MyApp/1.0',
        'Accept: application/json',
        'Authorization: Bearer token123',
        'X-API-Version: v2'
    ]
]);

$responses = $client->get(['https://api.example.com']);
?>
```

### Per-Request Headers

```php
<?php
$responses = $client->get(['https://api.example.com'], [
    'headers' => [
        'X-Custom-Header: value',
        'Content-Type: application/json'
    ]
]);
?>
```

### Header Array Format

Headers can be specified in multiple formats:

```php
<?php
// Array format (key => value)
$headers = [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer token123'
];

// String format (header: value)
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer token123'
];

// Mixed format
$headers = [
    'Content-Type' => 'application/json',
    'Authorization: Bearer token123',
    'X-Custom: value'
];
?>
```

## ⏱️ Timeout Configuration

### Global Timeout

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30  // 30 seconds for all requests
]);
?>
```

### Per-Request Timeout

```php
<?php
// Fast requests
$responses = $client->get(['https://api.example.com/quick'], [
    'timeout' => 5
]);

// Slow requests
$responses = $client->get(['https://api.example.com/slow'], [
    'timeout' => 120
]);
?>
```

### Request-Level Timeout

```php
<?php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com/quick')
        ->setOption('timeout', 5),
    Request::get('https://api.example.com/slow')
        ->setOption('timeout', 120)
];

$responses = $client->request('GET', $requests);
?>
```

## 🔄 Concurrency Configuration

### Global Concurrency

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'concurrency' => 5  // Maximum 5 parallel requests
]);
?>
```

### Per-Request Concurrency

```php
<?php
// High concurrency for many requests
$urls = array_fill(0, 50, 'https://api.example.com');
$responses = $client->get($urls, ['concurrency' => 20]);

// Low concurrency for rate-limited APIs
$responses = $client->get($urls, ['concurrency' => 2]);
?>
```

### Concurrency Best Practices

```php
<?php
// For public APIs (be respectful)
$client = new HttpClient(['concurrency' => 5]);

// For internal APIs (can be higher)
$client = new HttpClient(['concurrency' => 20]);

// For rate-limited APIs (be conservative)
$client = new HttpClient(['concurrency' => 2]);

// For testing (very low)
$client = new HttpClient(['concurrency' => 1]);
?>
```

## 📞 Callback Configuration

### Basic Callback

```php
<?php
use Phputils\Async\HttpClient;

$callback = function ($url, $response) {
    echo "Completed: $url - Status: {$response['status']}\n";
    
    if ($response['status'] === 200) {
        // Process successful response
        $data = json_decode($response['body'], true);
        // ... handle data
    }
};

$client = new HttpClient(['callback' => $callback]);
$responses = $client->get(['https://api.example.com']);
?>
```

### Advanced Callback with Context

```php
<?php
use Phputils\Async\HttpClient;

$processedCount = 0;
$errors = [];

$callback = function ($url, $response) use (&$processedCount, &$errors) {
    $processedCount++;
    
    if (!empty($response['error'])) {
        $errors[] = ['url' => $url, 'error' => $response['error']];
    } elseif ($response['status'] >= 400) {
        $errors[] = ['url' => $url, 'status' => $response['status']];
    }
    
    // Log progress
    if ($processedCount % 10 === 0) {
        echo "Processed $processedCount requests...\n";
    }
};

$client = new HttpClient(['callback' => $callback]);
$responses = $client->get($urls);

echo "Total processed: $processedCount\n";
echo "Errors: " . count($errors) . "\n";
?>
```

### Per-Request Callback

```php
<?php
$responses = $client->get(['https://api.example.com'], [
    'callback' => function ($url, $response) {
        // Custom callback for this specific request
        echo "Special handling for: $url\n";
    }
]);
?>
```

## 🔒 SSL Configuration

### SSL Verification

```php
<?php
use Phputils\Async\HttpClient;

// Default (secure)
$client = new HttpClient([
    'verify_ssl' => true,
    'verify_host' => 2
]);

// Disable SSL verification (not recommended for production)
$client = new HttpClient([
    'verify_ssl' => false,
    'verify_host' => 0
]);
?>
```

### Custom SSL Options

```php
<?php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com')
        ->setOption('CURLOPT_SSL_VERIFYPEER', true)
        ->setOption('CURLOPT_SSL_VERIFYHOST', 2)
        ->setOption('CURLOPT_CAINFO', '/path/to/ca-bundle.crt')
];
?>
```

## 🔄 Redirect Configuration

### Redirect Settings

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'follow_redirects' => true,    // Follow redirects
    'max_redirects' => 5           // Maximum redirects to follow
]);

// Disable redirects
$client = new HttpClient([
    'follow_redirects' => false,
    'max_redirects' => 0
]);
?>
```

## 🌐 Proxy Configuration

### HTTP Proxy

```php
<?php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com')
        ->setOption('CURLOPT_PROXY', 'http://proxy.example.com:8080')
        ->setOption('CURLOPT_PROXYUSERPWD', 'username:password')
];
?>
```

### SOCKS Proxy

```php
<?php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com')
        ->setOption('CURLOPT_PROXY', 'socks5://proxy.example.com:1080')
        ->setOption('CURLOPT_PROXYUSERPWD', 'username:password')
];
?>
```

## 📊 Advanced Configuration Examples

### Production Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30,
    'concurrency' => 10,
    'headers' => [
        'User-Agent: MyApp/1.0',
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate'
    ],
    'follow_redirects' => true,
    'max_redirects' => 5,
    'verify_ssl' => true,
    'verify_host' => 2,
    'callback' => function ($url, $response) {
        // Log completed requests
        error_log("Request completed: $url - {$response['status']}");
    }
]);
?>
```

### Development Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 10,
    'concurrency' => 5,
    'headers' => [
        'User-Agent: MyApp/1.0-dev'
    ],
    'verify_ssl' => false,  // For development only
    'callback' => function ($url, $response) {
        // Debug output
        echo "DEBUG: $url -> {$response['status']}\n";
    }
]);
?>
```

### High-Performance Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 60,
    'concurrency' => 50,  // High concurrency
    'headers' => [
        'User-Agent: MyApp/1.0',
        'Connection: keep-alive'
    ],
    'follow_redirects' => true,
    'max_redirects' => 3,
    'verify_ssl' => true
]);
?>
```

### Rate-Limited API Configuration

```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient([
    'timeout' => 30,
    'concurrency' => 2,  // Low concurrency to respect rate limits
    'headers' => [
        'User-Agent: MyApp/1.0',
        'Authorization: Bearer token123'
    ],
    'callback' => function ($url, $response) {
        // Check for rate limit headers
        if (isset($response['headers']['X-RateLimit-Remaining'])) {
            $remaining = $response['headers']['X-RateLimit-Remaining'];
            if ($remaining < 10) {
                // Slow down if approaching rate limit
                sleep(1);
            }
        }
    }
]);
?>
```

## 🔧 Custom cURL Options

For advanced use cases, you can set custom cURL options:

```php
<?php
use Phputils\Async\Request;

$requests = [
    Request::get('https://api.example.com')
        ->setOption('CURLOPT_CONNECTTIMEOUT', 10)
        ->setOption('CURLOPT_LOW_SPEED_TIME', 30)
        ->setOption('CURLOPT_LOW_SPEED_LIMIT', 1024)
        ->setOption('CURLOPT_HTTP_VERSION', CURL_HTTP_VERSION_2_0)
];
?>
```

## 📝 Configuration Best Practices

### 1. Environment-Based Configuration

```php
<?php
use Phputils\Async\HttpClient;

$config = [
    'timeout' => $_ENV['HTTP_TIMEOUT'] ?? 30,
    'concurrency' => $_ENV['HTTP_CONCURRENCY'] ?? 10,
    'verify_ssl' => $_ENV['APP_ENV'] !== 'development',
    'headers' => [
        'User-Agent: ' . ($_ENV['APP_NAME'] ?? 'MyApp') . '/1.0'
    ]
];

$client = new HttpClient($config);
?>
```

### 2. Configuration Validation

```php
<?php
function validateHttpConfig($config) {
    $required = ['timeout', 'concurrency'];
    $optional = ['headers', 'callback', 'user_agent'];
    
    foreach ($required as $key) {
        if (!isset($config[$key])) {
            throw new InvalidArgumentException("Missing required config: $key");
        }
    }
    
    return $config;
}

$config = validateHttpConfig([
    'timeout' => 30,
    'concurrency' => 10
]);

$client = new HttpClient($config);
?>
```

### 3. Configuration Factory

```php
<?php
class HttpClientFactory {
    public static function createForEnvironment($env = 'production') {
        $configs = [
            'development' => [
                'timeout' => 10,
                'concurrency' => 5,
                'verify_ssl' => false
            ],
            'testing' => [
                'timeout' => 5,
                'concurrency' => 2,
                'verify_ssl' => false
            ],
            'production' => [
                'timeout' => 30,
                'concurrency' => 10,
                'verify_ssl' => true
            ]
        ];
        
        return new HttpClient($configs[$env] ?? $configs['production']);
    }
}

$client = HttpClientFactory::createForEnvironment($_ENV['APP_ENV'] ?? 'production');
?>
```

## 🔗 Next Steps

Now that you understand configuration:

1. **Learn the API**: Check out the [API Reference](api-reference.md)
2. **See Examples**: Browse [Examples & Use Cases](examples.md)
3. **Optimize Performance**: Read the [Performance Guide](performance.md)
4. **Troubleshoot Issues**: Review the [Troubleshooting Guide](troubleshooting.md)

---

*For more advanced configuration options, see the [API Reference](api-reference.md)*
