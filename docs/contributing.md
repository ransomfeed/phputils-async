# Contributing Guide

Thank you for your interest in contributing to **phputils-async**! This guide will help you get started with contributing to the project.

## 📋 Table of Contents

- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Code Style](#code-style)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Issue Reporting](#issue-reporting)

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- Composer
- Git
- Basic understanding of HTTP and cURL

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
```bash
git clone https://github.com/your-username/phputils-async.git
cd phputils-async
```

3. Add the upstream repository:
```bash
git remote add upstream https://github.com/ransomfeed/phputils-async.git
```

## 🛠️ Development Setup

### Install Dependencies

```bash
# Install development dependencies
composer install

# Verify installation
composer test
```

### Development Workflow

1. **Create a branch:**
```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-number
```

2. **Make your changes**
3. **Run tests:**
```bash
composer test
```

4. **Check code style:**
```bash
composer cs-check
```

5. **Fix code style issues:**
```bash
composer cs-fix
```

6. **Run static analysis:**
```bash
composer phpstan
```

## 📝 Code Style

### PSR-12 Compliance

The project follows PSR-12 coding standards. Use the provided tools to ensure compliance:

```bash
# Check code style
composer cs-check

# Fix code style issues
composer cs-fix
```

### Code Style Guidelines

1. **Use type hints** where possible:
```php
public function get(array $urls, array $options = []): array
{
    // Implementation
}
```

2. **Document all public methods** with PHPDoc:
```php
/**
 * Execute GET requests asynchronously
 * 
 * @param array $urls Array of URLs to request
 * @param array $options Optional request-specific options
 * @return array Associative array with URLs as keys and responses as values
 */
public function get(array $urls, array $options = []): array
```

3. **Use meaningful variable names:**
```php
// Good
$responseTime = $response['info']['total_time'];

// Bad
$rt = $r['info']['total_time'];
```

4. **Handle errors gracefully:**
```php
if ($response === false) {
    return new Response(null, [], '', 'cURL error', []);
}
```

5. **Keep methods focused and small:**
```php
// Good - single responsibility
private function createCurlHandle(Request $request, array $options)
{
    // Implementation
}

// Bad - multiple responsibilities
private function createCurlHandleAndExecuteRequest(Request $request, array $options)
{
    // Too much responsibility
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test
./vendor/bin/phpunit tests/HttpClientTest.php

# Run specific test method
./vendor/bin/phpunit tests/HttpClientTest.php::testGetRequests
```

### Writing Tests

1. **Test file structure:**
```php
<?php

namespace Phputils\Async\Tests;

use Phputils\Async\HttpClient;
use PHPUnit\Framework\TestCase;

class YourTest extends TestCase
{
    private HttpClient $client;

    protected function setUp(): void
    {
        $this->client = new HttpClient();
    }

    public function testYourMethod(): void
    {
        // Arrange
        $input = 'test input';
        
        // Act
        $result = $this->client->yourMethod($input);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }
}
```

2. **Test naming conventions:**
- `testMethodName()` - Test a specific method
- `testMethodNameWithSpecificCondition()` - Test specific conditions
- `testMethodNameThrowsException()` - Test exception handling

3. **Test data providers:**
```php
/**
 * @dataProvider urlProvider
 */
public function testGetRequests($urls, $expectedCount): void
{
    $responses = $this->client->get($urls);
    $this->assertCount($expectedCount, $responses);
}

public function urlProvider(): array
{
    return [
        'single url' => [['https://httpbin.org/get'], 1],
        'multiple urls' => [['https://httpbin.org/get', 'https://httpbin.org/json'], 2],
    ];
}
```

4. **Mock external dependencies:**
```php
public function testWithMockedCurl(): void
{
    // Use httpbin.org for testing instead of real APIs
    $urls = ['https://httpbin.org/get'];
    $responses = $this->client->get($urls);
    
    $this->assertArrayHasKey('https://httpbin.org/get', $responses);
    $this->assertEquals(200, $responses['https://httpbin.org/get']['status']);
}
```

### Test Coverage

Aim for high test coverage:

```bash
# Generate coverage report
composer test-coverage

# View coverage report
open coverage/index.html
```

**Coverage targets:**
- Overall coverage: > 90%
- Critical paths: 100%
- Edge cases: > 80%

## 📤 Submitting Changes

### Pull Request Process

1. **Ensure your branch is up to date:**
```bash
git fetch upstream
git rebase upstream/main
```

2. **Run all quality checks:**
```bash
composer quality
```

3. **Commit your changes:**
```bash
git add .
git commit -m "Add feature: brief description

- Detailed description of changes
- List of improvements
- Any breaking changes

Closes #123"
```

4. **Push your branch:**
```bash
git push origin feature/your-feature-name
```

5. **Create a Pull Request** on GitHub

### Pull Request Guidelines

1. **Title:** Use a clear, descriptive title
   - `Add feature: HTTP/2 support`
   - `Fix bug: memory leak in async processing`
   - `Improve: optimize concurrency handling`

2. **Description:** Include:
   - What changes were made
   - Why the changes were necessary
   - How to test the changes
   - Any breaking changes
   - Related issues

3. **Code Review:** Be responsive to feedback and make requested changes

### Commit Message Format

```
Type: Brief description

Detailed description of changes
- List of improvements
- Any breaking changes

Closes #123
```

**Types:**
- `Add:` New features
- `Fix:` Bug fixes
- `Improve:` Performance improvements
- `Refactor:` Code refactoring
- `Test:` Test additions/changes
- `Docs:` Documentation updates

## 🐛 Issue Reporting

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Check the troubleshooting guide** for common solutions
3. **Verify the issue** with the latest version

### Bug Reports

Include the following information:

```markdown
**Bug Description**
A clear description of the bug.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What you expected to happen.

**Environment**
- PHP version: 7.4.0
- OS: Ubuntu 20.04
- phputils-async version: 1.0.0

**Code Sample**
```php
$client = new HttpClient();
$responses = $client->get(['https://example.com']);
```

**Error Output**
```
Fatal error: Class 'Phputils\Async\HttpClient' not found
```

**Additional Context**
Any other context about the problem.
```

### Feature Requests

Include the following information:

```markdown
**Feature Description**
A clear description of the feature you'd like to see.

**Use Case**
Describe the problem this feature would solve.

**Proposed Solution**
How you think this feature should work.

**Alternatives**
Any alternative solutions you've considered.

**Additional Context**
Any other context about the feature request.
```

## 🔧 Development Tools

### PHPStan

Static analysis tool for catching bugs:

```bash
# Run PHPStan
composer phpstan

# Fix issues found by PHPStan
```

### PHP CS Fixer

Code style fixer:

```bash
# Check code style
composer cs-check

# Fix code style issues
composer cs-fix
```

### Quality Checks

Run all quality checks:

```bash
composer quality
```

This runs:
- Code style check
- Static analysis
- Tests

## 📚 Documentation

### Updating Documentation

1. **Update relevant docs** when adding features
2. **Add examples** for new functionality
3. **Update API reference** for new methods
4. **Test documentation** examples

### Documentation Structure

```
docs/
├── README.md           # Documentation index
├── installation.md     # Installation guide
├── quick-start.md      # Quick start guide
├── configuration.md    # Configuration options
├── api-reference.md    # API documentation
├── examples.md         # Examples and use cases
├── performance.md      # Performance guide
├── troubleshooting.md  # Troubleshooting guide
└── contributing.md     # This file
```

## 🎯 Areas for Contribution

### High Priority

- **Performance optimizations**
- **Additional HTTP methods** (PATCH, HEAD, OPTIONS)
- **Request/response interceptors**
- **Better error handling**
- **Connection pooling**

### Medium Priority

- **Additional examples**
- **Documentation improvements**
- **Test coverage improvements**
- **CI/CD enhancements**

### Low Priority

- **Additional response formats**
- **Custom transport layers**
- **Advanced caching**

## 🤝 Community Guidelines

### Be Respectful

- Use welcoming and inclusive language
- Respect differing viewpoints and experiences
- Accept constructive criticism gracefully
- Focus on what's best for the community

### Be Collaborative

- Help others when possible
- Share knowledge and best practices
- Provide constructive feedback
- Work together to solve problems

### Be Professional

- Keep discussions on-topic
- Use clear and concise communication
- Follow the project's code of conduct
- Respect maintainers' decisions

## 📞 Getting Help

### Questions and Discussions

- **GitHub Discussions:** For general questions and discussions
- **GitHub Issues:** For bug reports and feature requests
- **Pull Requests:** For code contributions

### Contact Maintainers

- **GitHub:** @ransomfeed/phputils-async
- **Email:** nuke@spcnet.it

## 📄 License

By contributing to phputils-async, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to phputils-async! Your contributions help make the project better for everyone.
