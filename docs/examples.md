# Examples & Use Cases

Practical examples and real-world use cases for **phputils-async**. Learn how to solve common problems with asynchronous HTTP requests.

## 📋 Table of Contents

- [Basic Examples](#basic-examples)
- [API Integration](#api-integration)
- [Data Collection](#data-collection)
- [Web Scraping](#web-scraping)
- [Microservices](#microservices)
- [Rate Limiting](#rate-limiting)
- [Error Handling](#error-handling)
- [Performance Optimization](#performance-optimization)

## 🚀 Basic Examples

### Simple GET Requests

```php
<?php
require_once 'vendor/autoload.php';
use Phputils\Async\HttpClient;

$client = new HttpClient();
$urls = [
    'https://api.github.com',
    'https://httpbin.org/get',
    'https://api.agify.io?name=mario'
];

$responses = $client->get($urls);

foreach ($responses as $url => $response) {
    echo "URL: $url\n";
    echo "Status: {$response['status']}\n";
    echo "Body: " . substr($response['body'], 0, 100) . "...\n\n";
}
?>
```

### POST Requests with JSON Data

```php
<?php
use Phputils\Async\HttpClient;
use Phputils\Async\Request;

$client = new HttpClient();

// Method 1: Array format
$requests = [
    [
        'url' => 'https://httpbin.org/post',
        'body' => json_encode(['name' => 'John', 'age' => 30])
    ],
    [
        'url' => 'https://httpbin.org/post',
        'body' => json_encode(['name' => 'Jane', 'age' => 25])
    ]
];

$responses = $client->post($requests);

// Method 2: Request objects
$requestObjects = [
    Request::post('https://httpbin.org/post', json_encode(['name' => 'Bob']))
        ->addHeader('Content-Type', 'application/json'),
    Request::post('https://httpbin.org/post', json_encode(['name' => 'Alice']))
        ->addHeader('Content-Type', 'application/json')
];

$responses = $client->post($requestObjects);
?>
```

### Mixed HTTP Methods

```php
<?php
use Phputils\Async\HttpClient;
use Phputils\Async\Request;

$client = new HttpClient();

$requests = [
    Request::get('https://api.example.com/users'),
    Request::post('https://api.example.com/users', '{"name": "New User"}'),
    Request::put('https://api.example.com/users/1', '{"name": "Updated User"}'),
    Request::delete('https://api.example.com/users/2')
];

$responses = $client->request('GET', $requests);
?>
```

## 🔌 API Integration

### REST API Client

```php
<?php
class ApiClient {
    private $httpClient;
    private $baseUrl;
    private $apiKey;

    public function __construct($baseUrl, $apiKey) {
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'headers' => [
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function getUsers($userIds) {
        $requests = [];
        foreach ($userIds as $id) {
            $requests[] = Request::get($this->baseUrl . "/users/{$id}");
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function createUsers($users) {
        $requests = [];
        foreach ($users as $user) {
            $requests[] = Request::post(
                $this->baseUrl . '/users',
                json_encode($user)
            );
        }
        
        return $this->httpClient->post($requests);
    }

    public function updateUsers($updates) {
        $requests = [];
        foreach ($updates as $id => $userData) {
            $requests[] = Request::put(
                $this->baseUrl . "/users/{$id}",
                json_encode($userData)
            );
        }
        
        return $this->httpClient->request('PUT', $requests);
    }
}

// Usage
$apiClient = new ApiClient('https://api.example.com', 'your-api-key');

// Get multiple users
$userIds = [1, 2, 3, 4, 5];
$users = $apiClient->getUsers($userIds);

// Create multiple users
$newUsers = [
    ['name' => 'John Doe', 'email' => 'john@example.com'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com']
];
$createdUsers = $apiClient->createUsers($newUsers);
?>
```

### Multiple API Endpoints

```php
<?php
use Phputils\Async\HttpClient;
use Phputils\Async\Request;

$client = new HttpClient([
    'timeout' => 30,
    'concurrency' => 5
]);

// Collect data from multiple APIs
$requests = [
    Request::get('https://api.github.com/repos/octocat/Hello-World'),
    Request::get('https://api.agify.io?name=mario'),
    Request::get('https://api.genderize.io?name=mario'),
    Request::get('https://api.nationalize.io?name=mario'),
    Request::get('https://httpbin.org/json')
];

$responses = $client->request('GET', $requests);

$data = [];
foreach ($responses as $url => $response) {
    if ($response['status'] === 200) {
        $data[$url] = json_decode($response['body'], true);
    }
}

// Process collected data
echo "GitHub repo: " . $data['https://api.github.com/repos/octocat/Hello-World']['name'] . "\n";
echo "Age prediction: " . $data['https://api.agify.io?name=mario']['age'] . "\n";
echo "Gender prediction: " . $data['https://api.genderize.io?name=mario']['gender'] . "\n";
?>
```

## 📊 Data Collection

### Weather Data Aggregation

```php
<?php
class WeatherCollector {
    private $httpClient;
    private $apiKeys;

    public function __construct($apiKeys) {
        $this->httpClient = new HttpClient([
            'timeout' => 15,
            'concurrency' => 3 // Be respectful to weather APIs
        ]);
        $this->apiKeys = $apiKeys;
    }

    public function getWeatherForCities($cities) {
        $requests = [];
        
        foreach ($cities as $city) {
            // OpenWeatherMap
            $requests[] = Request::get(
                "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$this->apiKeys['openweather']}&units=metric"
            );
            
            // WeatherAPI (alternative)
            $requests[] = Request::get(
                "https://api.weatherapi.com/v1/current.json?key={$this->apiKeys['weatherapi']}&q={$city}"
            );
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function aggregateWeatherData($responses) {
        $aggregated = [];
        
        foreach ($responses as $url => $response) {
            if ($response['status'] === 200) {
                $data = json_decode($response['body'], true);
                
                if (strpos($url, 'openweathermap') !== false) {
                    $city = $data['name'];
                    $aggregated[$city]['openweather'] = [
                        'temp' => $data['main']['temp'],
                        'description' => $data['weather'][0]['description']
                    ];
                } elseif (strpos($url, 'weatherapi') !== false) {
                    $city = $data['location']['name'];
                    $aggregated[$city]['weatherapi'] = [
                        'temp' => $data['current']['temp_c'],
                        'description' => $data['current']['condition']['text']
                    ];
                }
            }
        }
        
        return $aggregated;
    }
}

// Usage
$weatherCollector = new WeatherCollector([
    'openweather' => 'your-openweather-api-key',
    'weatherapi' => 'your-weatherapi-key'
]);

$cities = ['London', 'Paris', 'Tokyo', 'New York'];
$responses = $weatherCollector->getWeatherForCities($cities);
$weatherData = $weatherCollector->aggregateWeatherData($responses);

foreach ($weatherData as $city => $data) {
    echo "Weather for $city:\n";
    if (isset($data['openweather'])) {
        echo "  OpenWeather: {$data['openweather']['temp']}°C - {$data['openweather']['description']}\n";
    }
    if (isset($data['weatherapi'])) {
        echo "  WeatherAPI: {$data['weatherapi']['temp']}°C - {$data['weatherapi']['description']}\n";
    }
}
?>
```

### Stock Market Data

```php
<?php
class StockDataCollector {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 10,
            'concurrency' => 5
        ]);
    }

    public function getStockPrices($symbols) {
        $requests = [];
        
        foreach ($symbols as $symbol) {
            // Alpha Vantage API
            $requests[] = Request::get(
                "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$symbol}&apikey=demo"
            );
            
            // Yahoo Finance (alternative)
            $requests[] = Request::get(
                "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}"
            );
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function processStockData($responses) {
        $stockData = [];
        
        foreach ($responses as $url => $response) {
            if ($response['status'] === 200) {
                $data = json_decode($response['body'], true);
                
                if (strpos($url, 'alphavantage') !== false && isset($data['Global Quote'])) {
                    $quote = $data['Global Quote'];
                    $symbol = $quote['01. symbol'];
                    $stockData[$symbol]['alphavantage'] = [
                        'price' => $quote['05. price'],
                        'change' => $quote['09. change'],
                        'change_percent' => $quote['10. change percent']
                    ];
                }
            }
        }
        
        return $stockData;
    }
}

// Usage
$stockCollector = new StockDataCollector();
$symbols = ['AAPL', 'GOOGL', 'MSFT', 'TSLA'];
$responses = $stockCollector->getStockPrices($symbols);
$stockData = $stockCollector->processStockData($responses);

foreach ($stockData as $symbol => $data) {
    echo "Stock: $symbol\n";
    if (isset($data['alphavantage'])) {
        $av = $data['alphavantage'];
        echo "  Price: \${$av['price']}\n";
        echo "  Change: {$av['change']} ({$av['change_percent']})\n";
    }
}
?>
```

## 🕷️ Web Scraping

### Product Price Monitoring

```php
<?php
class PriceMonitor {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'headers' => [
                'User-Agent: Mozilla/5.0 (compatible; PriceMonitor/1.0)',
                'Accept: text/html,application/xhtml+xml'
            ]
        ]);
    }

    public function checkPrices($products) {
        $requests = [];
        
        foreach ($products as $product) {
            $requests[] = Request::get($product['url']);
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function extractPrices($responses, $products) {
        $prices = [];
        
        foreach ($responses as $index => $response) {
            if ($response['status'] === 200) {
                $product = $products[$index];
                $html = $response['body'];
                
                // Extract price using regex (adjust based on target site)
                if (preg_match($product['price_regex'], $html, $matches)) {
                    $prices[$product['name']] = [
                        'price' => $matches[1],
                        'url' => $product['url'],
                        'timestamp' => time()
                    ];
                }
            }
        }
        
        return $prices;
    }
}

// Usage
$priceMonitor = new PriceMonitor();

$products = [
    [
        'name' => 'iPhone 15',
        'url' => 'https://www.apple.com/iphone-15/',
        'price_regex' => '/price.*?\$(\d+)/i'
    ],
    [
        'name' => 'MacBook Air',
        'url' => 'https://www.apple.com/macbook-air/',
        'price_regex' => '/from.*?\$(\d+)/i'
    ]
];

$responses = $priceMonitor->checkPrices($products);
$prices = $priceMonitor->extractPrices($responses, $products);

foreach ($prices as $name => $data) {
    echo "Product: $name\n";
    echo "Price: \${$data['price']}\n";
    echo "URL: {$data['url']}\n\n";
}
?>
```

### News Aggregator

```php
<?php
class NewsAggregator {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 20,
            'concurrency' => 3
        ]);
    }

    public function fetchNews($sources) {
        $requests = [];
        
        foreach ($sources as $source) {
            $requests[] = Request::get($source['url']);
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function parseNews($responses, $sources) {
        $news = [];
        
        foreach ($responses as $index => $response) {
            if ($response['status'] === 200) {
                $source = $sources[$index];
                $html = $response['body'];
                
                // Parse news items (adjust based on source structure)
                $news[$source['name']] = $this->extractNewsItems($html, $source);
            }
        }
        
        return $news;
    }

    private function extractNewsItems($html, $source) {
        // This is a simplified example - adjust based on actual HTML structure
        $items = [];
        
        // Example: Extract news items using regex or DOM parsing
        if (preg_match_all($source['item_pattern'], $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $items[] = [
                    'title' => $match[1],
                    'link' => $source['base_url'] . $match[2],
                    'source' => $source['name']
                ];
            }
        }
        
        return array_slice($items, 0, 5); // Limit to 5 items per source
    }
}

// Usage
$newsAggregator = new NewsAggregator();

$sources = [
    [
        'name' => 'BBC News',
        'url' => 'https://www.bbc.com/news',
        'base_url' => 'https://www.bbc.com',
        'item_pattern' => '/<h3[^>]*>.*?<a[^>]*href="([^"]*)"[^>]*>([^<]*)<\/a>/i'
    ],
    [
        'name' => 'CNN',
        'url' => 'https://www.cnn.com',
        'base_url' => 'https://www.cnn.com',
        'item_pattern' => '/<h3[^>]*>.*?<a[^>]*href="([^"]*)"[^>]*>([^<]*)<\/a>/i'
    ]
];

$responses = $newsAggregator->fetchNews($sources);
$news = $newsAggregator->parseNews($responses, $sources);

foreach ($news as $source => $items) {
    echo "=== $source ===\n";
    foreach ($items as $item) {
        echo "• {$item['title']}\n";
        echo "  Link: {$item['link']}\n\n";
    }
}
?>
```

## 🏗️ Microservices

### Service Health Check

```php
<?php
class ServiceHealthChecker {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 5,
            'concurrency' => 10
        ]);
    }

    public function checkServices($services) {
        $requests = [];
        
        foreach ($services as $service) {
            $requests[] = Request::get($service['health_url']);
        }
        
        return $this->httpClient->request('GET', $requests);
    }

    public function generateHealthReport($responses, $services) {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'services' => [],
            'summary' => [
                'total' => count($services),
                'healthy' => 0,
                'unhealthy' => 0,
                'unknown' => 0
            ]
        ];
        
        foreach ($responses as $index => $response) {
            $service = $services[$index];
            $status = $this->determineHealthStatus($response);
            
            $report['services'][$service['name']] = [
                'url' => $service['health_url'],
                'status' => $status,
                'response_time' => $response['info']['total_time'] ?? null,
                'http_code' => $response['status']
            ];
            
            $report['summary'][$status]++;
        }
        
        return $report;
    }

    private function determineHealthStatus($response) {
        if ($response['status'] === 200) {
            return 'healthy';
        } elseif ($response['status'] >= 500) {
            return 'unhealthy';
        } else {
            return 'unknown';
        }
    }
}

// Usage
$healthChecker = new ServiceHealthChecker();

$services = [
    ['name' => 'API Gateway', 'health_url' => 'https://api.example.com/health'],
    ['name' => 'User Service', 'health_url' => 'https://users.example.com/health'],
    ['name' => 'Payment Service', 'health_url' => 'https://payments.example.com/health'],
    ['name' => 'Notification Service', 'health_url' => 'https://notifications.example.com/health']
];

$responses = $healthChecker->checkServices($services);
$report = $healthChecker->generateHealthReport($responses, $services);

echo "Health Check Report - {$report['timestamp']}\n";
echo "Total Services: {$report['summary']['total']}\n";
echo "Healthy: {$report['summary']['healthy']}\n";
echo "Unhealthy: {$report['summary']['unhealthy']}\n";
echo "Unknown: {$report['summary']['unknown']}\n\n";

foreach ($report['services'] as $name => $data) {
    echo "$name: {$data['status']} (HTTP {$data['http_code']})\n";
}
?>
```

### Distributed Data Processing

```php
<?php
class DistributedProcessor {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 60,
            'concurrency' => 5
        ]);
    }

    public function processDataChunks($dataChunks, $workerNodes) {
        $requests = [];
        
        foreach ($dataChunks as $index => $chunk) {
            $worker = $workerNodes[$index % count($workerNodes)];
            $requests[] = Request::post(
                "http://{$worker}/process",
                json_encode(['data' => $chunk])
            );
        }
        
        return $this->httpClient->post($requests);
    }

    public function aggregateResults($responses) {
        $results = [];
        
        foreach ($responses as $url => $response) {
            if ($response['status'] === 200) {
                $data = json_decode($response['body'], true);
                $results[] = $data['result'];
            }
        }
        
        return $results;
    }
}

// Usage
$processor = new DistributedProcessor();

$dataChunks = [
    ['item1', 'item2', 'item3'],
    ['item4', 'item5', 'item6'],
    ['item7', 'item8', 'item9'],
    ['item10', 'item11', 'item12']
];

$workerNodes = [
    'worker1.example.com:8080',
    'worker2.example.com:8080',
    'worker3.example.com:8080'
];

$responses = $processor->processDataChunks($dataChunks, $workerNodes);
$results = $processor->aggregateResults($responses);

echo "Processed " . count($results) . " chunks successfully\n";
?>
```

## 🚦 Rate Limiting

### API Rate Limiting

```php
<?php
class RateLimitedClient {
    private $httpClient;
    private $rateLimits;
    private $requestCounts;

    public function __construct($rateLimits) {
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'concurrency' => 1, // Start with low concurrency
            'callback' => [$this, 'handleResponse']
        ]);
        $this->rateLimits = $rateLimits;
        $this->requestCounts = [];
    }

    public function handleResponse($url, $response) {
        $domain = parse_url($url, PHP_URL_HOST);
        
        if (!isset($this->requestCounts[$domain])) {
            $this->requestCounts[$domain] = 0;
        }
        
        $this->requestCounts[$domain]++;
        
        // Check rate limit headers
        if (isset($response['headers']['X-RateLimit-Remaining'])) {
            $remaining = (int)$response['headers']['X-RateLimit-Remaining'];
            
            if ($remaining < 5) {
                // Slow down if approaching rate limit
                sleep(1);
            }
        }
    }

    public function makeRequests($urls) {
        // Group URLs by domain
        $groupedUrls = [];
        foreach ($urls as $url) {
            $domain = parse_url($url, PHP_URL_HOST);
            $groupedUrls[$domain][] = $url;
        }
        
        $allResponses = [];
        
        foreach ($groupedUrls as $domain => $domainUrls) {
            $limit = $this->rateLimits[$domain] ?? 10;
            
            // Process in batches to respect rate limits
            $batches = array_chunk($domainUrls, $limit);
            
            foreach ($batches as $batch) {
                $responses = $this->httpClient->get($batch);
                $allResponses = array_merge($allResponses, $responses);
                
                // Wait between batches
                sleep(1);
            }
        }
        
        return $allResponses;
    }
}

// Usage
$rateLimitedClient = new RateLimitedClient([
    'api.github.com' => 5,
    'api.twitter.com' => 3,
    'api.example.com' => 10
]);

$urls = [
    'https://api.github.com/repos/octocat/Hello-World',
    'https://api.github.com/users/octocat',
    'https://api.github.com/repos/microsoft/vscode',
    'https://api.example.com/data1',
    'https://api.example.com/data2'
];

$responses = $rateLimitedClient->makeRequests($urls);
?>
```

## 🚨 Error Handling

### Robust Error Handling

```php
<?php
class RobustHttpClient {
    private $httpClient;
    private $retryAttempts;
    private $retryDelay;

    public function __construct($retryAttempts = 3, $retryDelay = 1) {
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'concurrency' => 5
        ]);
        $this->retryAttempts = $retryAttempts;
        $this->retryDelay = $retryDelay;
    }

    public function getWithRetry($urls) {
        $allResponses = [];
        $failedUrls = $urls;
        $attempt = 0;
        
        while (!empty($failedUrls) && $attempt < $this->retryAttempts) {
            $attempt++;
            echo "Attempt $attempt for " . count($failedUrls) . " URLs\n";
            
            $responses = $this->httpClient->get($failedUrls);
            $newFailedUrls = [];
            
            foreach ($responses as $url => $response) {
                if ($this->isSuccess($response)) {
                    $allResponses[$url] = $response;
                } else {
                    $newFailedUrls[] = $url;
                    echo "Failed: $url - Status: {$response['status']}\n";
                }
            }
            
            $failedUrls = $newFailedUrls;
            
            if (!empty($failedUrls) && $attempt < $this->retryAttempts) {
                echo "Retrying in {$this->retryDelay} seconds...\n";
                sleep($this->retryDelay);
                $this->retryDelay *= 2; // Exponential backoff
            }
        }
        
        return $allResponses;
    }

    private function isSuccess($response) {
        return $response['status'] >= 200 && $response['status'] < 300;
    }
}

// Usage
$robustClient = new RobustHttpClient(3, 1);

$urls = [
    'https://httpbin.org/status/200',
    'https://httpbin.org/status/500', // This will fail
    'https://httpbin.org/get',
    'https://httpbin.org/status/503'  // This will also fail
];

$responses = $robustClient->getWithRetry($urls);

echo "Successfully processed " . count($responses) . " out of " . count($urls) . " URLs\n";
?>
```

## ⚡ Performance Optimization

### Batch Processing

```php
<?php
class BatchProcessor {
    private $httpClient;
    private $batchSize;

    public function __construct($batchSize = 100) {
        $this->httpClient = new HttpClient([
            'timeout' => 60,
            'concurrency' => 20
        ]);
        $this->batchSize = $batchSize;
    }

    public function processInBatches($urls) {
        $batches = array_chunk($urls, $this->batchSize);
        $allResponses = [];
        
        foreach ($batches as $batchIndex => $batch) {
            echo "Processing batch " . ($batchIndex + 1) . " of " . count($batches) . "\n";
            
            $responses = $this->httpClient->get($batch);
            $allResponses = array_merge($allResponses, $responses);
            
            // Memory management
            if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
                gc_collect_cycles();
            }
        }
        
        return $allResponses;
    }
}

// Usage
$processor = new BatchProcessor(50);

$urls = [];
for ($i = 1; $i <= 1000; $i++) {
    $urls[] = "https://httpbin.org/delay/1?request=$i";
}

$responses = $processor->processInBatches($urls);
echo "Processed " . count($responses) . " URLs\n";
?>
```

### Memory-Efficient Processing

```php
<?php
class MemoryEfficientProcessor {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'concurrency' => 10,
            'callback' => [$this, 'processResponse']
        ]);
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
    }

    private function saveData($url, $data) {
        // Save to file or database
        file_put_contents(
            'data/' . md5($url) . '.json',
            json_encode($data),
            FILE_APPEND
        );
    }

    public function processUrls($urls) {
        // Process URLs without storing all responses in memory
        $this->httpClient->get($urls);
    }
}

// Usage
$processor = new MemoryEfficientProcessor();

$urls = [
    'https://api.github.com/repos/octocat/Hello-World',
    'https://api.github.com/users/octocat',
    'https://httpbin.org/json'
];

$processor->processUrls($urls);
echo "Data saved to files\n";
?>
```

## 🔗 Next Steps

Now that you've seen practical examples:

1. **Explore Configuration**: Check out [Configuration Options](configuration.md)
2. **Study the API**: Review the [API Reference](api-reference.md)
3. **Optimize Performance**: Read the [Performance Guide](performance.md)
4. **Troubleshoot Issues**: See the [Troubleshooting Guide](troubleshooting.md)

---

*For more advanced examples and patterns, check out the [Performance Guide](performance.md)*
