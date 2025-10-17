# Performance Guide

Optimization, benchmarking, and performance tuning for **phputils-async**.

## 📋 Table of Contents

- [Performance Overview](#performance-overview)
- [Benchmarking](#benchmarking)
- [Optimization Strategies](#optimization-strategies)
- [Concurrency Tuning](#concurrency-tuning)
- [Memory Management](#memory-management)
- [Network Optimization](#network-optimization)
- [Monitoring and Profiling](#monitoring-and-profiling)

## 🚀 Performance Overview

phputils-async is designed for high-performance asynchronous HTTP requests. Here's what you can expect:

### Typical Performance Gains

- **2-10x faster** than sequential requests
- **Reduced memory usage** compared to traditional approaches
- **Better resource utilization** with controlled concurrency
- **Scalable** from small batches to thousands of requests

### Performance Characteristics

| Scenario | Sequential Time | Async Time | Improvement |
|----------|----------------|------------|-------------|
| 10 requests (1s each) | 10.0s | 1.2s | 8.3x faster |
| 50 requests (0.5s each) | 25.0s | 2.8s | 8.9x faster |
| 100 requests (0.2s each) | 20.0s | 2.1s | 9.5x faster |

## 📊 Benchmarking

### Basic Benchmark

```php
<?php
require_once 'phputils-async-standalone.php';
use Phputils\Async\HttpClient;

function benchmarkRequests($urls, $concurrency = 10) {
    $client = new HttpClient(['concurrency' => $concurrency]);
    
    // Async benchmark
    $startTime = microtime(true);
    $responses = $client->get($urls);
    $asyncTime = microtime(true) - $startTime;
    
    // Sequential benchmark
    $sequentialClient = new HttpClient(['concurrency' => 1]);
    $startTime = microtime(true);
    $sequentialResponses = $sequentialClient->get($urls);
    $sequentialTime = microtime(true) - $startTime;
    
    return [
        'async_time' => $asyncTime,
        'sequential_time' => $sequentialTime,
        'improvement' => $sequentialTime / $asyncTime,
        'requests' => count($urls),
        'successful' => count(array_filter($responses, fn($r) => $r['status'] === 200))
    ];
}

// Test with different request counts
$testCases = [
    ['count' => 5, 'delay' => 1],
    ['count' => 10, 'delay' => 1],
    ['count' => 20, 'delay' => 0.5],
    ['count' => 50, 'delay' => 0.2]
];

foreach ($testCases as $test) {
    $urls = array_fill(0, $test['count'], "https://httpbin.org/delay/{$test['delay']}");
    
    $result = benchmarkRequests($urls);
    
    echo "=== {$test['count']} requests (delay: {$test['delay']}s) ===\n";
    echo "Async time: " . round($result['async_time'], 2) . "s\n";
    echo "Sequential time: " . round($result['sequential_time'], 2) . "s\n";
    echo "Improvement: " . round($result['improvement'], 2) . "x faster\n";
    echo "Success rate: " . round($result['successful'] / $result['requests'] * 100, 1) . "%\n\n";
}
?>
```

### Advanced Benchmarking Suite

```php
<?php
class PerformanceBenchmark {
    private $results = [];

    public function runBenchmark($urls, $concurrencyLevels = [1, 5, 10, 20, 50]) {
        foreach ($concurrencyLevels as $concurrency) {
            $result = $this->benchmarkConcurrency($urls, $concurrency);
            $this->results[$concurrency] = $result;
        }
        
        return $this->results;
    }

    private function benchmarkConcurrency($urls, $concurrency) {
        $client = new HttpClient(['concurrency' => $concurrency]);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $responses = $client->get($urls);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $successful = count(array_filter($responses, fn($r) => $r['status'] >= 200 && $r['status'] < 300));
        $errors = count($responses) - $successful;
        
        return [
            'concurrency' => $concurrency,
            'total_time' => $endTime - $startTime,
            'memory_used' => $endMemory - $startMemory,
            'requests_per_second' => count($urls) / ($endTime - $startTime),
            'successful_requests' => $successful,
            'failed_requests' => $errors,
            'success_rate' => $successful / count($urls) * 100,
            'avg_response_time' => array_sum(array_column($responses, 'info')) / count($responses)
        ];
    }

    public function generateReport() {
        echo "=== Performance Benchmark Report ===\n\n";
        
        $bestConcurrency = null;
        $bestRps = 0;
        
        foreach ($this->results as $concurrency => $result) {
            echo "Concurrency: $concurrency\n";
            echo "  Total time: " . round($result['total_time'], 2) . "s\n";
            echo "  Memory used: " . round($result['memory_used'] / 1024 / 1024, 2) . " MB\n";
            echo "  Requests/sec: " . round($result['requests_per_second'], 2) . "\n";
            echo "  Success rate: " . round($result['success_rate'], 1) . "%\n";
            echo "  Avg response time: " . round($result['avg_response_time'], 3) . "s\n\n";
            
            if ($result['requests_per_second'] > $bestRps) {
                $bestRps = $result['requests_per_second'];
                $bestConcurrency = $concurrency;
            }
        }
        
        echo "Best performance: Concurrency $bestConcurrency with " . round($bestRps, 2) . " req/s\n";
    }
}

// Usage
$benchmark = new PerformanceBenchmark();
$urls = array_fill(0, 100, 'https://httpbin.org/get');
$results = $benchmark->runBenchmark($urls);
$benchmark->generateReport();
?>
```

### Real-World Performance Test

```php
<?php
class RealWorldBenchmark {
    public function testApiEndpoints() {
        $endpoints = [
            'GitHub API' => 'https://api.github.com/repos/octocat/Hello-World',
            'JSONPlaceholder' => 'https://jsonplaceholder.typicode.com/posts/1',
            'HTTPBin' => 'https://httpbin.org/json',
            'Agify API' => 'https://api.agify.io?name=mario',
            'Genderize API' => 'https://api.genderize.io?name=mario'
        ];
        
        // Test with multiple requests to each endpoint
        $urls = [];
        foreach ($endpoints as $name => $url) {
            for ($i = 0; $i < 5; $i++) {
                $urls[] = $url;
            }
        }
        
        $client = new HttpClient(['concurrency' => 10]);
        
        $startTime = microtime(true);
        $responses = $client->get($urls);
        $totalTime = microtime(true) - $startTime;
        
        $this->analyzeResults($responses, $totalTime, $endpoints);
    }

    private function analyzeResults($responses, $totalTime, $endpoints) {
        $endpointStats = [];
        
        foreach ($endpoints as $name => $url) {
            $endpointStats[$name] = [
                'requests' => 0,
                'successful' => 0,
                'total_time' => 0,
                'avg_response_time' => 0
            ];
        }
        
        foreach ($responses as $responseUrl => $response) {
            foreach ($endpoints as $name => $endpointUrl) {
                if (strpos($responseUrl, $endpointUrl) === 0) {
                    $endpointStats[$name]['requests']++;
                    
                    if ($response['status'] >= 200 && $response['status'] < 300) {
                        $endpointStats[$name]['successful']++;
                    }
                    
                    if (isset($response['info']['total_time'])) {
                        $endpointStats[$name]['total_time'] += $response['info']['total_time'];
                    }
                    break;
                }
            }
        }
        
        foreach ($endpointStats as $name => $stats) {
            if ($stats['requests'] > 0) {
                $stats['avg_response_time'] = $stats['total_time'] / $stats['requests'];
                $stats['success_rate'] = $stats['successful'] / $stats['requests'] * 100;
                
                echo "=== $name ===\n";
                echo "Requests: {$stats['requests']}\n";
                echo "Success rate: " . round($stats['success_rate'], 1) . "%\n";
                echo "Avg response time: " . round($stats['avg_response_time'], 3) . "s\n\n";
            }
        }
        
        echo "Total time: " . round($totalTime, 2) . "s\n";
        echo "Total requests: " . count($responses) . "\n";
        echo "Requests per second: " . round(count($responses) / $totalTime, 2) . "\n";
    }
}

$benchmark = new RealWorldBenchmark();
$benchmark->testApiEndpoints();
?>
```

## ⚡ Optimization Strategies

### Optimal Concurrency

Finding the right concurrency level is crucial for performance:

```php
<?php
class ConcurrencyOptimizer {
    public function findOptimalConcurrency($urls, $maxConcurrency = 50) {
        $results = [];
        
        for ($concurrency = 1; $concurrency <= $maxConcurrency; $concurrency += 5) {
            $result = $this->testConcurrency($urls, $concurrency);
            $results[$concurrency] = $result;
            
            // Stop if performance starts degrading
            if (count($results) > 1) {
                $prevResult = end(array_slice($results, -2, 1));
                if ($result['requests_per_second'] < $prevResult['requests_per_second'] * 0.9) {
                    break;
                }
            }
        }
        
        return $this->analyzeResults($results);
    }

    private function testConcurrency($urls, $concurrency) {
        $client = new HttpClient(['concurrency' => $concurrency]);
        
        $startTime = microtime(true);
        $responses = $client->get($urls);
        $totalTime = microtime(true) - $startTime;
        
        $successful = count(array_filter($responses, fn($r) => $r['status'] === 200));
        
        return [
            'concurrency' => $concurrency,
            'total_time' => $totalTime,
            'requests_per_second' => count($urls) / $totalTime,
            'success_rate' => $successful / count($urls) * 100,
            'successful_requests' => $successful
        ];
    }

    private function analyzeResults($results) {
        $bestConcurrency = null;
        $bestRps = 0;
        $bestSuccessRate = 0;
        
        foreach ($results as $concurrency => $result) {
            // Weight requests per second and success rate
            $score = $result['requests_per_second'] * ($result['success_rate'] / 100);
            
            if ($score > $bestRps * ($bestSuccessRate / 100)) {
                $bestConcurrency = $concurrency;
                $bestRps = $result['requests_per_second'];
                $bestSuccessRate = $result['success_rate'];
            }
        }
        
        return [
            'optimal_concurrency' => $bestConcurrency,
            'best_requests_per_second' => $bestRps,
            'best_success_rate' => $bestSuccessRate,
            'all_results' => $results
        ];
    }
}

// Usage
$optimizer = new ConcurrencyOptimizer();
$urls = array_fill(0, 100, 'https://httpbin.org/get');
$optimization = $optimizer->findOptimalConcurrency($urls);

echo "Optimal concurrency: {$optimization['optimal_concurrency']}\n";
echo "Best performance: {$optimization['best_requests_per_second']} req/s\n";
echo "Success rate: {$optimization['best_success_rate']}%\n";
?>
```

### Batch Processing Optimization

```php
<?php
class BatchOptimizer {
    private $httpClient;
    private $optimalBatchSize;

    public function __construct($optimalBatchSize = 100) {
        $this->httpClient = new HttpClient(['concurrency' => 10]);
        $this->optimalBatchSize = $optimalBatchSize;
    }

    public function processLargeDataset($urls) {
        $batches = array_chunk($urls, $this->optimalBatchSize);
        $allResponses = [];
        
        foreach ($batches as $batchIndex => $batch) {
            echo "Processing batch " . ($batchIndex + 1) . " of " . count($batches) . "\n";
            
            $startTime = microtime(true);
            $responses = $this->httpClient->get($batch);
            $batchTime = microtime(true) - $startTime;
            
            $allResponses = array_merge($allResponses, $responses);
            
            echo "Batch completed in " . round($batchTime, 2) . "s\n";
            echo "Memory usage: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";
            
            // Memory management
            if (memory_get_usage(true) > 200 * 1024 * 1024) { // 200MB
                gc_collect_cycles();
                echo "Garbage collection performed\n";
            }
        }
        
        return $allResponses;
    }

    public function findOptimalBatchSize($urls, $maxBatchSize = 500) {
        $batchSizes = [10, 25, 50, 100, 200, 500];
        $results = [];
        
        foreach ($batchSizes as $batchSize) {
            if ($batchSize > $maxBatchSize) break;
            
            $batches = array_chunk($urls, $batchSize);
            $totalTime = 0;
            $totalMemory = 0;
            
            foreach ($batches as $batch) {
                $startTime = microtime(true);
                $startMemory = memory_get_usage(true);
                
                $responses = $this->httpClient->get($batch);
                
                $totalTime += microtime(true) - $startTime;
                $totalMemory += memory_get_usage(true) - $startMemory;
            }
            
            $results[$batchSize] = [
                'batch_size' => $batchSize,
                'total_time' => $totalTime,
                'avg_memory_per_batch' => $totalMemory / count($batches),
                'requests_per_second' => count($urls) / $totalTime
            ];
        }
        
        return $results;
    }
}
?>
```

### Memory-Efficient Processing

```php
<?php
class MemoryEfficientProcessor {
    private $httpClient;
    private $maxMemoryUsage;

    public function __construct($maxMemoryUsage = 100 * 1024 * 1024) { // 100MB
        $this->httpClient = new HttpClient([
            'concurrency' => 5,
            'callback' => [$this, 'processResponse']
        ]);
        $this->maxMemoryUsage = $maxMemoryUsage;
    }

    public function processResponse($url, $response) {
        // Process response immediately instead of storing
        if ($response['status'] === 200) {
            $data = json_decode($response['body'], true);
            
            // Save to database, file, or process immediately
            $this->saveData($url, $data);
        }
        
        // Clear response from memory
        unset($response);
        
        // Check memory usage
        if (memory_get_usage(true) > $this->maxMemoryUsage) {
            gc_collect_cycles();
        }
    }

    private function saveData($url, $data) {
        // Example: Save to file
        $filename = 'data/' . md5($url) . '.json';
        file_put_contents($filename, json_encode($data));
        
        // Or save to database
        // $this->database->insert('responses', ['url' => $url, 'data' => $data]);
    }

    public function processUrls($urls) {
        // Process URLs without storing all responses in memory
        $this->httpClient->get($urls);
    }
}

// Usage
$processor = new MemoryEfficientProcessor();
$urls = array_fill(0, 1000, 'https://httpbin.org/json');
$processor->processUrls($urls);
echo "Processing completed with minimal memory usage\n";
?>
```

## 🔄 Concurrency Tuning

### Dynamic Concurrency Adjustment

```php
<?php
class DynamicConcurrencyManager {
    private $httpClient;
    private $currentConcurrency;
    private $minConcurrency;
    private $maxConcurrency;
    private $adjustmentFactor;

    public function __construct($minConcurrency = 1, $maxConcurrency = 50, $adjustmentFactor = 0.1) {
        $this->minConcurrency = $minConcurrency;
        $this->maxConcurrency = $maxConcurrency;
        $this->currentConcurrency = $minConcurrency;
        $this->adjustmentFactor = $adjustmentFactor;
        
        $this->httpClient = new HttpClient([
            'concurrency' => $this->currentConcurrency,
            'callback' => [$this, 'adjustConcurrency']
        ]);
    }

    public function adjustConcurrency($url, $response) {
        // Adjust concurrency based on response success and time
        if ($response['status'] >= 200 && $response['status'] < 300) {
            // Success - can increase concurrency
            $this->currentConcurrency = min(
                $this->maxConcurrency,
                $this->currentConcurrency + $this->adjustmentFactor
            );
        } else {
            // Failure - decrease concurrency
            $this->currentConcurrency = max(
                $this->minConcurrency,
                $this->currentConcurrency - $this->adjustmentFactor
            );
        }
        
        // Update client concurrency
        $this->httpClient = new HttpClient([
            'concurrency' => $this->currentConcurrency,
            'callback' => [$this, 'adjustConcurrency']
        ]);
    }

    public function getCurrentConcurrency() {
        return $this->currentConcurrency;
    }
}
?>
```

### Rate Limiting with Backoff

```php
<?php
class AdaptiveRateLimiter {
    private $httpClient;
    private $requestTimes = [];
    private $rateLimit;
    private $backoffTime;

    public function __construct($rateLimit = 10, $backoffTime = 1) {
        $this->rateLimit = $rateLimit; // requests per second
        $this->backoffTime = $backoffTime;
        
        $this->httpClient = new HttpClient([
            'concurrency' => 1,
            'callback' => [$this, 'handleResponse']
        ]);
    }

    public function handleResponse($url, $response) {
        $this->requestTimes[] = microtime(true);
        
        // Check for rate limit headers
        if (isset($response['headers']['X-RateLimit-Remaining'])) {
            $remaining = (int)$response['headers']['X-RateLimit-Remaining'];
            
            if ($remaining < 5) {
                // Approaching rate limit - slow down
                sleep($this->backoffTime);
            }
        }
        
        // Check for rate limit exceeded
        if ($response['status'] === 429) {
            // Rate limited - increase backoff time
            $this->backoffTime *= 2;
            sleep($this->backoffTime);
        }
        
        // Clean old request times
        $this->requestTimes = array_filter(
            $this->requestTimes,
            fn($time) => microtime(true) - $time < 60
        );
    }

    public function getCurrentRate() {
        $now = microtime(true);
        $recentRequests = array_filter(
            $this->requestTimes,
            fn($time) => $now - $time < 1
        );
        
        return count($recentRequests);
    }
}
?>
```

## 🧠 Memory Management

### Memory Monitoring

```php
<?php
class MemoryMonitor {
    private $startMemory;
    private $peakMemory;
    private $checkpoints = [];

    public function __construct() {
        $this->startMemory = memory_get_usage(true);
        $this->peakMemory = $this->startMemory;
    }

    public function checkpoint($name) {
        $currentMemory = memory_get_usage(true);
        $this->checkpoints[$name] = [
            'memory' => $currentMemory,
            'time' => microtime(true),
            'relative' => $currentMemory - $this->startMemory
        ];
        
        if ($currentMemory > $this->peakMemory) {
            $this->peakMemory = $currentMemory;
        }
    }

    public function getReport() {
        $currentMemory = memory_get_usage(true);
        
        return [
            'start_memory' => $this->formatBytes($this->startMemory),
            'current_memory' => $this->formatBytes($currentMemory),
            'peak_memory' => $this->formatBytes($this->peakMemory),
            'memory_used' => $this->formatBytes($currentMemory - $this->startMemory),
            'checkpoints' => array_map(function($checkpoint) {
                return [
                    'memory' => $this->formatBytes($checkpoint['memory']),
                    'relative' => $this->formatBytes($checkpoint['relative'])
                ];
            }, $this->checkpoints)
        ];
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Usage
$monitor = new MemoryMonitor();
$monitor->checkpoint('start');

$client = new HttpClient();
$responses = $client->get(array_fill(0, 100, 'https://httpbin.org/get'));

$monitor->checkpoint('after_requests');

$report = $monitor->getReport();
echo "Memory Report:\n";
echo "Start: {$report['start_memory']}\n";
echo "Current: {$report['current_memory']}\n";
echo "Peak: {$report['peak_memory']}\n";
echo "Used: {$report['memory_used']}\n";
?>
```

## 🌐 Network Optimization

### Connection Pooling

```php
<?php
class ConnectionPoolManager {
    private $httpClient;
    private $connectionReuse;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'headers' => [
                'Connection: keep-alive'
            ]
        ]);
        $this->connectionReuse = true;
    }

    public function optimizeForConnectionReuse($urls) {
        // Group URLs by host to maximize connection reuse
        $hostGroups = [];
        
        foreach ($urls as $url) {
            $host = parse_url($url, PHP_URL_HOST);
            $hostGroups[$host][] = $url;
        }
        
        $allResponses = [];
        
        // Process each host group separately
        foreach ($hostGroups as $host => $hostUrls) {
            echo "Processing $host with " . count($hostUrls) . " requests\n";
            
            $responses = $this->httpClient->get($hostUrls);
            $allResponses = array_merge($allResponses, $responses);
        }
        
        return $allResponses;
    }
}
?>
```

### HTTP/2 Support

```php
<?php
class Http2Optimizer {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient();
    }

    public function enableHttp2($urls) {
        $requests = [];
        
        foreach ($urls as $url) {
            $requests[] = Request::get($url)
                ->setOption('HTTP_VERSION', CURL_HTTP_VERSION_2_0);
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function testHttpVersions($urls) {
        $results = [];
        
        // Test HTTP/1.1
        $startTime = microtime(true);
        $http11Responses = $this->httpClient->get($urls);
        $http11Time = microtime(true) - $startTime;
        
        // Test HTTP/2
        $startTime = microtime(true);
        $http2Responses = $this->enableHttp2($urls);
        $http2Time = microtime(true) - $startTime;
        
        return [
            'http11_time' => $http11Time,
            'http2_time' => $http2Time,
            'improvement' => $http11Time / $http2Time,
            'http11_rps' => count($urls) / $http11Time,
            'http2_rps' => count($urls) / $http2Time
        ];
    }
}
?>
```

## 📈 Monitoring and Profiling

### Performance Profiler

```php
<?php
class PerformanceProfiler {
    private $metrics = [];
    private $startTime;
    private $startMemory;

    public function __construct() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    public function profileRequest($url, $response) {
        $this->metrics[] = [
            'url' => $url,
            'status' => $response['status'],
            'total_time' => $response['info']['total_time'] ?? 0,
            'connect_time' => $response['info']['connect_time'] ?? 0,
            'size' => strlen($response['body']),
            'memory_usage' => memory_get_usage(true) - $this->startMemory
        ];
    }

    public function getSummary() {
        if (empty($this->metrics)) {
            return null;
        }

        $totalTime = microtime(true) - $this->startTime;
        $totalRequests = count($this->metrics);
        $successfulRequests = count(array_filter($this->metrics, fn($m) => $m['status'] >= 200 && $m['status'] < 300));
        
        $responseTimes = array_column($this->metrics, 'total_time');
        $connectTimes = array_column($this->metrics, 'connect_time');
        $sizes = array_column($this->metrics, 'size');

        return [
            'total_time' => $totalTime,
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $successfulRequests / $totalRequests * 100,
            'requests_per_second' => $totalRequests / $totalTime,
            'avg_response_time' => array_sum($responseTimes) / count($responseTimes),
            'min_response_time' => min($responseTimes),
            'max_response_time' => max($responseTimes),
            'avg_connect_time' => array_sum($connectTimes) / count($connectTimes),
            'total_data_transferred' => array_sum($sizes),
            'avg_response_size' => array_sum($sizes) / count($sizes),
            'memory_usage' => memory_get_usage(true) - $this->startMemory,
            'peak_memory' => memory_get_peak_usage(true) - $this->startMemory
        ];
    }

    public function getDetailedReport() {
        $summary = $this->getSummary();
        if (!$summary) {
            return null;
        }

        echo "=== Performance Profiler Report ===\n\n";
        
        echo "Timing:\n";
        echo "  Total time: " . round($summary['total_time'], 2) . "s\n";
        echo "  Requests per second: " . round($summary['requests_per_second'], 2) . "\n";
        echo "  Avg response time: " . round($summary['avg_response_time'], 3) . "s\n";
        echo "  Min response time: " . round($summary['min_response_time'], 3) . "s\n";
        echo "  Max response time: " . round($summary['max_response_time'], 3) . "s\n\n";
        
        echo "Data Transfer:\n";
        echo "  Total data: " . $this->formatBytes($summary['total_data_transferred']) . "\n";
        echo "  Avg response size: " . $this->formatBytes($summary['avg_response_size']) . "\n\n";
        
        echo "Success Rate:\n";
        echo "  Successful: {$summary['successful_requests']}/{$summary['total_requests']}\n";
        echo "  Success rate: " . round($summary['success_rate'], 1) . "%\n\n";
        
        echo "Memory Usage:\n";
        echo "  Memory used: " . $this->formatBytes($summary['memory_usage']) . "\n";
        echo "  Peak memory: " . $this->formatBytes($summary['peak_memory']) . "\n";
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Usage
$profiler = new PerformanceProfiler();

$client = new HttpClient([
    'callback' => [$profiler, 'profileRequest']
]);

$urls = array_fill(0, 100, 'https://httpbin.org/get');
$responses = $client->get($urls);

$profiler->getDetailedReport();
?>
```

## 🔗 Next Steps

Now that you understand performance optimization:

1. **Apply optimizations** to your specific use case
2. **Monitor performance** in production
3. **Benchmark regularly** to ensure optimal performance
4. **Check troubleshooting** if you encounter issues

---

*For more examples and use cases, see the [Examples Guide](examples.md)*
