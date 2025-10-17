# Installation Guide

This guide covers all available installation methods for **phputils-async**, from Composer to simple file upload.

## 📋 Requirements

Before installing phputils-async, ensure your environment meets these requirements:

- **PHP**: 7.4 or higher
- **Extensions**: cURL extension (usually included by default)
- **Memory**: At least 128MB (recommended: 256MB+)
- **Disk Space**: ~50KB for the library files

### Checking Requirements

```bash
# Check PHP version
php --version

# Check if cURL is available
php -m | grep curl
```

Or in PHP:
```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "cURL Available: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
?>
```

## 🎯 Installation Methods

### Method 1: Composer (Recommended)

Composer is the preferred method for modern PHP projects.

#### Installation

```bash
# Add to your project
composer require phputility/async

# Or add to composer.json manually
{
    "require": {
        "phputility/async": "^1.0"
    }
}
```

#### Usage with Composer

```php
<?php
require_once 'vendor/autoload.php';

use Phputils\Async\HttpClient;

$client = new HttpClient();
$responses = $client->get(['https://api.example.com']);
?>
```

#### Benefits
- ✅ Automatic dependency management
- ✅ PSR-4 autoloading
- ✅ Version constraints
- ✅ Easy updates
- ✅ IDE support

### Method 2: Single File (Standalone)

Perfect for shared hosting or simple projects without Composer.

#### Download

Download the standalone file:
```bash
wget https://raw.githubusercontent.com/ransomfeed/phputils-async/main/phputils-async-standalone.php
```

Or copy the file from the project repository.

#### Usage

```php
<?php
// Include the standalone file
require_once 'phputils-async-standalone.php';

use Phputils\Async\HttpClient;

$client = new HttpClient();
$responses = $client->get(['https://api.example.com']);
?>
```

#### Benefits
- ✅ Single file - easy to upload
- ✅ No Composer required
- ✅ Works on any hosting
- ✅ Zero dependencies

### Method 3: Manual File Upload

For environments where Composer isn't available.

#### Step 1: Download Source Files

Download or copy these files to your project:
```
src/Phputils/Async/
├── HttpClient.php
├── Request.php
└── Response.php
```

#### Step 2: Include Files

```php
<?php
// Include all required files
require_once 'src/Phputils/Async/HttpClient.php';
require_once 'src/Phputils/Async/Request.php';
require_once 'src/Phputils/Async/Response.php';

use Phputils\Async\HttpClient;

$client = new HttpClient();
$responses = $client->get(['https://api.example.com']);
?>
```

#### Benefits
- ✅ Full control over files
- ✅ No external dependencies
- ✅ Works on any hosting
- ✅ Easy to customize

### Method 4: Custom Autoloader

Create your own autoloader for manual installations.

#### Create Autoloader

```php
<?php
// autoload.php
spl_autoload_register(function ($class) {
    $prefix = 'Phputils\\Async\\';
    $base_dir = __DIR__ . '/src/Phputils/Async/';
    
    // Check if class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require $file;
    }
});
?>
```

#### Usage

```php
<?php
require_once 'autoload.php';

use Phputils\Async\HttpClient;

$client = new HttpClient();
$responses = $client->get(['https://api.example.com']);
?>
```

## 🏗️ Project Structure Examples

### Composer Project Structure

```
my-project/
├── composer.json
├── composer.lock
├── vendor/
│   └── phputils/
│       └── async/
│           └── src/
├── src/
│   └── MyClass.php
└── index.php
```

### Standalone Project Structure

```
my-project/
├── phputils-async-standalone.php
├── src/
│   └── MyClass.php
└── index.php
```

### Manual Upload Structure

```
my-project/
├── src/
│   └── Phputils/
│       └── Async/
│           ├── HttpClient.php
│           ├── Request.php
│           └── Response.php
├── src/
│   └── MyClass.php
└── index.php
```

## 🔧 Installation Verification

After installation, verify everything works:

```php
<?php
// Include your chosen method
require_once 'vendor/autoload.php'; // Composer
// OR
require_once 'phputils-async-standalone.php'; // Standalone
// OR
require_once 'src/Phputils/Async/HttpClient.php'; // Manual

use Phputils\Async\HttpClient;

try {
    $client = new HttpClient();
    echo "✅ phputils-async loaded successfully!\n";
    echo "Async support: " . ($client->isAsyncAvailable() ? 'Yes' : 'No') . "\n";
    
    // Test a simple request
    $responses = $client->get(['https://httpbin.org/get']);
    echo "✅ Test request completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
```

## 🚀 Next Steps

After successful installation:

1. **Quick Start**: Follow the [Quick Start Guide](quick-start.md)
2. **Configuration**: Review [Configuration Options](configuration.md)
3. **Examples**: Check out [Examples & Use Cases](examples.md)
4. **API Reference**: Study the [API Documentation](api-reference.md)

## ❓ Troubleshooting Installation

### Common Issues

#### "Class not found" Error
```php
// Solution: Ensure proper autoloading
require_once 'vendor/autoload.php'; // For Composer
// OR include all files manually
require_once 'src/Phputils/Async/HttpClient.php';
require_once 'src/Phputils/Async/Request.php';
require_once 'src/Phputils/Async/Response.php';
```

#### cURL Extension Missing
```bash
# Install cURL extension
# Ubuntu/Debian
sudo apt-get install php-curl

# CentOS/RHEL
sudo yum install php-curl

# macOS with Homebrew
brew install php --with-curl
```

#### Memory Limit Issues
```php
// Increase memory limit
ini_set('memory_limit', '256M');
```

#### Permission Issues
```bash
# Fix file permissions
chmod 644 src/Phputils/Async/*.php
```

### Getting Help

If you encounter issues:

1. Check the [Troubleshooting Guide](troubleshooting.md)
2. Open an [issue](https://github.com/phputils/async/issues)
3. Join the [discussions](https://github.com/phputils/async/discussions)

---

*For more detailed information, continue to the [Quick Start Guide](quick-start.md)*
