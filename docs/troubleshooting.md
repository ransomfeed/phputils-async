# Troubleshooting Guide

Common issues, solutions, and debugging tips for **phputils-async**.

## 📋 Table of Contents

- [Common Issues](#common-issues)
- [Installation Problems](#installation-problems)
- [Configuration Issues](#configuration-issues)
- [Performance Problems](#performance-problems)
- [Error Handling](#error-handling)
- [Debugging Tips](#debugging-tips)
- [Getting Help](#getting-help)

## 🚨 Common Issues

### "Class not found" Error

**Problem:** Fatal error: Class 'Phputils\Async\HttpClient' not found

**Solutions:**

1. **Check autoloading:**
```php
<?php
// For Composer
require_once 'vendor/autoload.php';

// For standalone
require_once 'phputils-async-standalone.php';

// For manual files
require_once 'src/Phputils/Async/HttpClient.php';
require_once 'src/Phputils/Async/Request.php';
require_once 'src/Phputils/Async/Response.php';
```

2. **Verify file paths:**
```php
<?php
// Check if files exist
$files = [
    'vendor/autoload.php',
    'phputils-async-standalone.php',
    'src/Phputils/Async/HttpClient.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file not found\n";
    }
}
```

### cURL Extension Missing

**Problem:** cURL functions not available

**Solutions:**

1. **Check if cURL is installed:**
```php
<?php
if (extension_loaded('curl')) {
    echo "✅ cURL extension is loaded\n";
} else {
    echo "❌ cURL extension is not loaded\n";
}

// Check specific functions
$functions = ['curl_init', 'curl_multi_init', 'curl_exec'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func available\n";
    } else {
        echo "❌ $func not available\n";
    }
}
```

2. **Install cURL extension:**
```bash
# Ubuntu/Debian
sudo apt-get install php-curl
sudo systemctl restart apache2  # or nginx

# CentOS/RHEL
sudo yum install php-curl
sudo systemctl restart httpd

# macOS with Homebrew
brew install php --with-curl

# Windows (XAMPP/WAMP)
# Enable extension=curl in php.ini
```

### Memory Limit Exceeded

**Problem:** Fatal error: Allowed memory size exhausted

**Solutions:**

1. **Increase memory limit:**
```php
<?php
// In your script
ini_set('memory_limit', '256M');

// Or in php.ini
memory_limit = 256M
```

2. **Process in smaller batches:**
```php
<?php
use Phputils\Async\HttpClient;

$client = new HttpClient(['concurrency' => 5]); // Reduce concurrency

// Process in batches
$urls = array_fill(0, 1000, 'https://httpbin.org/get');
$batches = array_chunk($urls, 100);

foreach ($batches as $batch) {
    $responses = $client->get($batch);
    // Process batch
    unset($responses); // Free memory
    gc_collect_cycles(); // Force garbage collection
}
```

### Timeout Issues

**Problem:** Requests timing out

**Solutions:**

1. **Increase timeout:**
```php
<?php
$client = new HttpClient([
    'timeout' => 60  // Increase from default 30 seconds
]);

// Or per request
$responses = $client->get($urls, ['timeout' => 120]);
```

2. **Check network connectivity:**
```php
<?php
// Test connectivity
$testUrls = [
    'https://httpbin.org/get',
    'https://api.github.com',
    'https://google.com'
];

foreach ($testUrls as $url) {
    $start = microtime(true);
    $response = file_get_contents($url);
    $time = microtime(true) - $start;
    
    if ($response !== false) {
        echo "✅ $url: " . round($time, 2) . "s\n";
    } else {
        echo "❌ $url: Failed\n";
    }
}
```

## 🔧 Installation Problems

### Composer Issues

**Problem:** Composer not found or not working

**Solutions:**

1. **Install Composer:**
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

2. **Use standalone version:**
```php
<?php
// Instead of Composer, use standalone file
require_once 'phputils-async-standalone.php';
```

3. **Manual installation:**
```php
<?php
// Include files manually
require_once 'src/Phputils/Async/HttpClient.php';
require_once 'src/Phputils/Async/Request.php';
require_once 'src/Phputils/Async/Response.php';
```

### File Permission Issues

**Problem:** Permission denied errors

**Solutions:**

1. **Fix file permissions:**
```bash
# Make files readable
chmod 644 src/Phputils/Async/*.php
chmod 644 phputils-async-standalone.php

# Make directories readable
chmod 755 src/
chmod 755 src/Phputils/
chmod 755 src/Phputils/Async/
```

2. **Check web server permissions:**
```bash
# For Apache/Nginx
sudo chown -R www-data:www-data /path/to/your/project
sudo chmod -R 755 /path/to/your/project
```

## ⚙️ Configuration Issues

### Headers Not Working

**Problem:** Custom headers not being sent

**Solutions:**

1. **Check header format:**
```php
<?php
// Correct format
$client = new HttpClient([
    'headers' => [
        'Authorization: Bearer token123',  // String format
        'Content-Type' => 'application/json'  // Array format
    ]
]);

// Or using Request objects
$request = Request::get('https://api.example.com')
    ->addHeader('Authorization', 'Bearer token123');
```

2. **Debug headers:**
```php
<?php
$client = new HttpClient([
    'headers' => ['X-Debug: true']
]);

$responses = $client->get(['https://httpbin.org/headers']);

foreach ($responses as $url => $response) {
    $data = json_decode($response['body'], true);
    echo "Sent headers: " . json_encode($data['headers']) . "\n";
}
```

### Concurrency Issues

**Problem:** Too many concurrent requests causing errors

**Solutions:**

1. **Reduce concurrency:**
```php
<?php
$client = new HttpClient([
    'concurrency' => 2  // Reduce from default 10
]);
```

2. **Implement rate limiting:**
```php
<?php
class RateLimitedClient {
    private $httpClient;
    private $delay;

    public function __construct($delay = 1) {
        $this->httpClient = new HttpClient([
            'concurrency' => 1,
            'callback' => [$this, 'handleResponse']
        ]);
        $this->delay = $delay;
    }

    public function handleResponse($url, $response) {
        // Add delay between requests
        sleep($this->delay);
    }
}
```

### SSL Certificate Issues

**Problem:** SSL verification errors

**Solutions:**

1. **Disable SSL verification (development only):**
```php
<?php
$client = new HttpClient([
    'verify_ssl' => false,
    'verify_host' => 0
]);
```

2. **Update CA certificates:**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ca-certificates

# CentOS/RHEL
sudo yum update ca-certificates
```

3. **Custom CA bundle:**
```php
<?php
use Phputils\Async\Request;

$request = Request::get('https://api.example.com')
    ->setOption('CURLOPT_CAINFO', '/path/to/ca-bundle.crt');
```

## ⚡ Performance Problems

### Slow Requests

**Problem:** Requests taking too long

**Solutions:**

1. **Check network latency:**
```php
<?php
$urls = ['https://httpbin.org/delay/1'];
$start = microtime(true);

$client = new HttpClient();
$responses = $client->get($urls);

$time = microtime(true) - $start;
echo "Request took: " . round($time, 2) . " seconds\n";
```

2. **Optimize concurrency:**
```php
<?php
// Test different concurrency levels
$concurrencyLevels = [1, 5, 10, 20];
$urls = array_fill(0, 20, 'https://httpbin.org/get');

foreach ($concurrencyLevels as $level) {
    $client = new HttpClient(['concurrency' => $level]);
    
    $start = microtime(true);
    $responses = $client->get($urls);
    $time = microtime(true) - $start;
    
    echo "Concurrency $level: " . round($time, 2) . "s\n";
}
```

### High Memory Usage

**Problem:** Memory usage too high

**Solutions:**

1. **Monitor memory usage:**
```php
<?php
echo "Initial memory: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";

$client = new HttpClient();
$responses = $client->get($urls);

echo "After requests: " . memory_get_usage(true) / 1024 / 1024 . " MB\n";
echo "Peak memory: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";
```

2. **Process responses immediately:**
```php
<?php
$client = new HttpClient([
    'callback' => function ($url, $response) {
        // Process response immediately
        $data = json_decode($response['body'], true);
        // Save to database or file
        // Don't store in memory
    }
]);

$responses = $client->get($urls);
// responses array will be empty, but data is processed
```

## 🚨 Error Handling

### HTTP Error Codes

**Problem:** Getting 4xx or 5xx errors

**Solutions:**

1. **Handle different status codes:**
```php
<?php
$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    switch ($response['status']) {
        case 200:
            echo "✅ Success: $url\n";
            break;
        case 401:
            echo "❌ Unauthorized: $url - Check API key\n";
            break;
        case 403:
            echo "❌ Forbidden: $url - Check permissions\n";
            break;
        case 404:
            echo "❌ Not found: $url\n";
            break;
        case 429:
            echo "⚠️ Rate limited: $url - Slow down\n";
            break;
        case 500:
            echo "❌ Server error: $url - Try again later\n";
            break;
        default:
            echo "❌ Error {$response['status']}: $url\n";
    }
}
```

2. **Retry failed requests:**
```php
<?php
function retryFailedRequests($client, $urls, $maxRetries = 3) {
    $allResponses = [];
    $failedUrls = $urls;
    
    for ($attempt = 0; $attempt < $maxRetries && !empty($failedUrls); $attempt++) {
        $responses = $client->get($failedUrls);
        $newFailedUrls = [];
        
        foreach ($responses as $url => $response) {
            if ($response['status'] >= 200 && $response['status'] < 300) {
                $allResponses[$url] = $response;
            } else {
                $newFailedUrls[] = $url;
            }
        }
        
        $failedUrls = $newFailedUrls;
        
        if (!empty($failedUrls) && $attempt < $maxRetries - 1) {
            sleep(pow(2, $attempt)); // Exponential backoff
        }
    }
    
    return $allResponses;
}
```

### Network Errors

**Problem:** Connection errors, timeouts

**Solutions:**

1. **Check network connectivity:**
```php
<?php
function checkConnectivity($urls) {
    foreach ($urls as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($result === false) {
            echo "❌ $url: $error\n";
        } else {
            echo "✅ $url: HTTP $httpCode\n";
        }
    }
}
```

2. **Handle different error types:**
```php
<?php
$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    if (!empty($response['error'])) {
        // cURL error
        if (strpos($response['error'], 'timeout') !== false) {
            echo "⏰ Timeout: $url\n";
        } elseif (strpos($response['error'], 'resolve') !== false) {
            echo "🌐 DNS error: $url\n";
        } elseif (strpos($response['error'], 'connect') !== false) {
            echo "🔌 Connection error: $url\n";
        } else {
            echo "❌ cURL error: $url - {$response['error']}\n";
        }
    } elseif ($response['status'] === null) {
        echo "❌ No response: $url\n";
    }
}
```

## 🔍 Debugging Tips

### Enable Debug Mode

```php
<?php
$client = new HttpClient([
    'callback' => function ($url, $response) {
        echo "DEBUG: $url\n";
        echo "  Status: {$response['status']}\n";
        echo "  Time: {$response['info']['total_time']}s\n";
        echo "  Size: " . strlen($response['body']) . " bytes\n";
        
        if (!empty($response['error'])) {
            echo "  Error: {$response['error']}\n";
        }
        
        echo "\n";
    }
]);
```

### Log Requests and Responses

```php
<?php
class LoggingHttpClient {
    private $httpClient;
    private $logFile;

    public function __construct($logFile = 'http.log') {
        $this->httpClient = new HttpClient([
            'callback' => [$this, 'logResponse']
        ]);
        $this->logFile = $logFile;
    }

    public function logResponse($url, $response) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $url,
            'status' => $response['status'],
            'time' => $response['info']['total_time'] ?? 0,
            'size' => strlen($response['body']),
            'error' => $response['error'] ?? null
        ];
        
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }

    public function get($urls) {
        return $this->httpClient->get($urls);
    }
}
```

### Performance Profiling

```php
<?php
class ProfilingHttpClient {
    private $httpClient;
    private $metrics;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'callback' => [$this, 'recordMetrics']
        ]);
        $this->metrics = [];
    }

    public function recordMetrics($url, $response) {
        $this->metrics[] = [
            'url' => $url,
            'status' => $response['status'],
            'total_time' => $response['info']['total_time'] ?? 0,
            'connect_time' => $response['info']['connect_time'] ?? 0,
            'size' => strlen($response['body'])
        ];
    }

    public function getMetrics() {
        return $this->metrics;
    }

    public function getStats() {
        if (empty($this->metrics)) {
            return null;
        }

        $totalTime = array_sum(array_column($this->metrics, 'total_time'));
        $avgTime = $totalTime / count($this->metrics);
        $totalSize = array_sum(array_column($this->metrics, 'size'));

        return [
            'total_requests' => count($this->metrics),
            'total_time' => $totalTime,
            'average_time' => $avgTime,
            'total_size' => $totalSize,
            'success_rate' => count(array_filter($this->metrics, fn($m) => $m['status'] >= 200 && $m['status'] < 300)) / count($this->metrics) * 100
        ];
    }
}
```

## 🆘 Getting Help

### Before Asking for Help

1. **Check this troubleshooting guide**
2. **Verify your setup:**
```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "cURL Available: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
```

3. **Test with minimal example:**
```php
<?php
require_once 'phputils-async-standalone.php';
use Phputils\Async\HttpClient;

try {
    $client = new HttpClient();
    echo "✅ Client created successfully\n";
    
    $responses = $client->get(['https://httpbin.org/get']);
    echo "✅ Test request completed\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

### Reporting Issues

When reporting issues, include:

1. **PHP version and environment**
2. **Installation method used**
3. **Minimal code to reproduce the issue**
4. **Expected vs actual behavior**
5. **Error messages and stack traces**

### Resources

- [GitHub Issues](https://github.com/ransomfeed/phputils-async/issues)
- [GitHub Discussions](https://github.com/ransomfeed/phputils-async/discussions)
- [API Reference](api-reference.md)
- [Examples](examples.md)

---

*For more detailed information, see the [API Reference](api-reference.md) or [Examples](examples.md)*
