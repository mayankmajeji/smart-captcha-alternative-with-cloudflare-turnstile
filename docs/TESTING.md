# Testing Guide for SmartCT

This comprehensive guide explains how to test the SmartCT WordPress plugin, covering test execution, testing strategies, and quality assurance standards.

## Table of Contents

- [Quick Start](#quick-start)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Test Suites](#test-suites)
- [Testing Strategy](#testing-strategy)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [Test Configuration](#test-configuration)
- [Debugging](#debugging)
- [Best Practices](#best-practices)
- [Resources](#resources)

---

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Build Codeception
vendor/bin/codecept build

# Run all tests
npm run test:all

# Or run specific test suites
composer run test:phpcs      # PHP coding standards
composer run test:phpstan    # PHP static analysis
npm run test:unit            # Unit tests
npm run test:integration     # Integration tests
```

---

## Prerequisites

Before running tests, ensure you have the following installed:

- **PHP 7.4 or higher**
- **Composer** - For PHP dependency management
- **Node.js 18 or higher** - For JavaScript tooling
- **MySQL/MariaDB** - For integration tests
- **WordPress** - For integration and acceptance tests

### Optional Tools

- **ChromeDriver or Selenium** - For acceptance tests
- **Xdebug** - For code coverage
- **WP-CLI** - For WordPress Plugin Check

---

## Installation

### 1. Install PHP Dependencies

```bash
composer install
```

This installs:
- Codeception (testing framework)
- PHPCS (coding standards)
- PHPStan (static analysis)
- WordPress testing tools

### 2. Install Node.js Dependencies

```bash
npm install
```

This installs:
- ESLint (JavaScript linting)
- Stylelint (CSS linting)
- HTMLHint (HTML linting)
- Gulp (build tools)

### 3. Build Codeception

```bash
vendor/bin/codecept build
```

This generates helper classes for test suites.

---

## Test Suites

### 1. Code Linting

#### PHP Linting (PHPCS)

Runs WordPress Coding Standards checks:

```bash
composer run lint
# or
composer run test:phpcs
```

**What it checks:**
- WordPress coding standards compliance
- PHP compatibility (PHP 7.4+)
- Text domain usage
- Security best practices
- Hook naming conventions

**Configuration:** `phpcs.xml.dist`

#### PHP Static Analysis (PHPStan)

Runs static analysis to catch potential bugs:

```bash
composer run test:phpstan
```

**What it checks:**
- Type safety
- Undefined variables
- Invalid method calls
- Return type mismatches
- Level 5 analysis on `includes/` directory

**Configuration:** `phpstan.neon`

#### JavaScript Linting (ESLint)

Lints JavaScript files:

```bash
npm run lint:js
```

**Files checked:**
- `assets/js/admin.js`
- `assets/js/admin-settings.js`
- `assets/js/woocommerce.js`

**Standards:** WordPress JavaScript standards (`@wordpress/eslint-plugin`)

#### CSS/SCSS Linting (Stylelint)

Lints stylesheet files:

```bash
npm run lint:css
```

**Files checked:**
- All SCSS files in `assets/css/`
- Module files in `assets/css/modules/`

**Standards:** WordPress CSS standards (`@wordpress/stylelint-config`)

#### HTML Linting (HTMLHint)

Lints HTML template files:

```bash
npm run lint:html
```

**Files checked:**
- Admin templates in `includes/admin/templates/`

#### Run All Linting

```bash
npm run lint:all
```

Runs PHP, JavaScript, CSS, and HTML linting in sequence.

---

### 2. Unit Tests

Unit tests test individual classes and methods in isolation.

**Run unit tests:**

```bash
npm run test:unit
# or
vendor/bin/codecept run unit
```

**Test Coverage:**

- **Init class** - Singleton pattern, initialization
- **Settings class** - Settings retrieval, storage, validation
- **Verify class** - Token verification logic
- **Turnstile class** - Widget rendering, script enqueuing
- **Loader class** - Autoloader functionality
- **Ajax_Handlers class** - AJAX handler methods

**Location:** `tests/unit/`

---

### 3. Integration Tests

Integration tests verify the plugin's interaction with WordPress core.

**Run integration tests:**

```bash
npm run test:integration
# or
composer run test:integration
```

**Test Coverage:**

- Plugin activation/deactivation
- Settings persistence
- Option storage/retrieval
- AJAX handlers
- WordPress hooks and filters
- Form integration hooks
- WooCommerce integration
- Form plugin integrations

**Configuration:**

Integration tests require a WordPress installation. Configure database settings in `tests/integration.suite.yml` or use environment variables:

- `WP_ROOT_FOLDER` - Path to WordPress installation
- `TEST_DB_NAME` - Test database name
- `TEST_DB_HOST` - Database host
- `TEST_DB_USER` - Database user
- `TEST_DB_PASSWORD` - Database password

**Location:** `tests/integration/`

---

### 4. Acceptance Tests

Acceptance tests verify the plugin from a user's perspective using a browser.

**Run acceptance tests:**

```bash
npm run test:acceptance
# or
composer run test:acceptance
```

**Test Coverage:**

- Admin settings page rendering
- Form widget rendering
- User interactions
- Key verification flow
- Settings save functionality
- Browser compatibility

**Requirements:**

- WebDriver (Chrome/Firefox)
- Selenium Server or ChromeDriver
- Configured test environment

**Setup WebDriver:**

1. Install ChromeDriver:
   ```bash
   npm install -g chromedriver
   ```

2. Update `tests/acceptance.suite.yml` with WebDriver configuration

3. Ensure test site URL is accessible

**Location:** `tests/acceptance/`

---

### 5. WordPress Plugin Check

Runs the official WordPress Plugin Check tool to verify plugin compliance.

**Run plugin check:**

```bash
composer run test:plugin-check
```

**Or via WP-CLI:**

```bash
wp plugin check turnstilewp/turnstilewp.php --require=wp-content/plugins/plugin-check/cli.php
```

**What it checks:**

- Plugin headers
- Deprecated WordPress functions
- Security best practices
- Coding standards compliance
- Accessibility
- Performance
- i18n implementation

---

## Testing Strategy

### WordPress Standards Compliance

**Files to check:**
- `turnstilewp.php` (main plugin file)
- All files in `includes/` directory
- All integration classes in `includes/integrations/`
- Admin templates in `includes/admin/`

**Verification:**
- WordPress 5.8+ compatibility
- PHP 7.4+ compatibility
- Proper text domain usage (`turnstilewp`)
- Security best practices (nonces, sanitization, escaping)

### Browser Testing

Test in multiple browsers:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

**Responsive Testing:**
- Admin interface on mobile devices
- Turnstile widget on mobile forms
- Widget sizing and positioning
- Form layouts on various screen sizes

### Compatibility Testing

**WordPress Versions:**
- WordPress 5.8 (minimum required)
- WordPress 6.5 (tested up to)
- Latest WordPress version

**PHP Versions:**
- PHP 7.4 (minimum required)
- PHP 8.0, 8.1, 8.2, 8.3

**Plugin Compatibility:**
- Test with WooCommerce active/inactive
- Test with each form plugin individually
- Test with popular WordPress plugins
- Test with caching plugins

**Theme Compatibility:**
- Default WordPress themes
- Popular themes (Astra, GeneratePress, etc.)
- Test CSS conflicts

### Performance Testing

**Script Loading:**
- Verify scripts only load when needed
- Test script defer option
- Check for unnecessary script enqueuing
- Measure page load impact

**API Performance:**
- Test API response times
- Test timeout handling
- Monitor API call frequency

**Database Queries:**
- Check for N+1 query problems
- Verify efficient option retrieval
- Test settings caching

### Security Testing

**Input Validation:**
- Test nonce verification on all AJAX handlers
- Test capability checks (`manage_options`)
- Test input sanitization
- Test output escaping

**API Security:**
- Verify secret key is never exposed to frontend
- Test token validation
- Test IP address handling

**XSS Prevention:**
- Test all user inputs for XSS vulnerabilities
- Verify all outputs are escaped
- Test admin interface for XSS

**CSRF Protection:**
- Verify nonces on all forms
- Test AJAX nonce verification
- Test settings form nonces

### Accessibility Testing

**WCAG Compliance:**
- Test keyboard navigation in admin
- Test screen reader compatibility
- Test color contrast
- Test focus indicators

**Form Accessibility:**
- Verify Turnstile widget is accessible
- Test with screen readers
- Test keyboard-only navigation

---

## Running All Tests

Run all tests and linting:

```bash
npm run test:all
```

This will:
1. Run all linting checks (PHP, JS, CSS, HTML)
2. Run PHPCS and PHPStan
3. Run unit tests
4. Run integration tests

---

## Test Configuration

### Environment Variables

Set these in your environment or `tests/_envs/local.yml`:

```yaml
WP_ROOT_FOLDER: /path/to/wordpress
TEST_DB_NAME: turnstilewp_test
TEST_DB_HOST: localhost
TEST_DB_USER: root
TEST_DB_PASSWORD: password
TEST_TABLE_PREFIX: wp_
TEST_SITE_DOMAIN: turnstilewp.test
TEST_SITE_ADMIN_EMAIL: admin@turnstilewp.test
TEST_SITE_URL: http://turnstilewp.test
```

### Codeception Configuration

**Main configuration:** `codeception.yml`

**Suite configurations:**
- `tests/unit.suite.yml` - Unit tests
- `tests/integration.suite.yml` - Integration tests
- `tests/acceptance.suite.yml` - Acceptance tests

### Test Data

Test data files are stored in `tests/_data/`. Database dumps can be placed here for integration tests.

---

## Continuous Integration

Tests run automatically on GitHub Actions when:
- Code is pushed to `main` or `develop` branches
- Pull requests are opened

**Configuration:** `.github/workflows/tests.yml` (to be created)

---

## Writing Tests

### Unit Tests

Create test files in `tests/unit/`:

```php
<?php
namespace SmartCT\Tests\Unit;

use SmartCT\YourClass;
use Codeception\Test\Unit;

class YourClassTest extends Unit
{
    protected $tester;
    
    protected function _before()
    {
        // Setup before each test
    }
    
    public function testYourMethod()
    {
        $instance = new YourClass();
        $result = $instance->yourMethod();
        $this->assertTrue($result);
    }
}
```

### Integration Tests

Create test files in `tests/integration/`:

```php
<?php
namespace SmartCT\Tests\Integration;

use Codeception\TestCase\WPTestCase;

class YourIntegrationTest extends WPTestCase
{
    public function testWordPressIntegration()
    {
        // Test WordPress integration
        $this->assertTrue(function_exists('add_action'));
    }
}
```

### Acceptance Tests

Create test files in `tests/acceptance/`:

```php
<?php
namespace SmartCT\Tests\Acceptance;

use AcceptanceTester;

class YourAcceptanceCest
{
    public function testUserFlow(AcceptanceTester $I)
    {
        $I->amOnPage('/wp-admin');
        $I->see('Dashboard');
    }
}
```

---

## Debugging

### Verbose Output

```bash
vendor/bin/codecept run --debug
```

### Run Specific Test

```bash
vendor/bin/codecept run unit SettingsTest
```

### Run Specific Test Method

```bash
vendor/bin/codecept run unit SettingsTest:testGetOption
```

### Generate Code Coverage

```bash
vendor/bin/codecept run unit --coverage --coverage-html
```

Coverage report will be generated in `tests/_output/coverage/`

---

## Troubleshooting

### Tests Fail to Connect to Database

- Verify database credentials in suite configuration
- Ensure MySQL service is running
- Check database exists and is accessible
- Verify `TEST_DB_*` environment variables

### Acceptance Tests Fail

- Verify WebDriver is installed and running
- Check browser configuration in `acceptance.suite.yml`
- Ensure test site URL is accessible
- Check ChromeDriver version matches Chrome version

### PHPStan Errors

- Some WordPress function errors are expected and ignored
- Check `phpstan.neon` for ignored error patterns
- Update ignore patterns if needed
- Use `@phpstan-ignore-next-line` for specific cases

### Linting Fails

- Run `composer run format` to auto-fix PHP issues
- Run `npm run fix:js` to auto-fix JavaScript issues
- Run `npm run fix:css` to auto-fix CSS issues
- Check configuration files for custom rules

---

## Best Practices

1. **Write tests first** - Use TDD when possible
2. **Keep tests isolated** - Each test should be independent
3. **Use descriptive names** - Test names should describe what they test
4. **Test edge cases** - Don't just test happy paths
5. **Mock external dependencies** - Use mocks for API calls, database, etc.
6. **Clean up after tests** - Use `_before()` and `_after()` methods
7. **Run tests frequently** - Run tests before committing code
8. **Keep tests fast** - Unit tests should run in milliseconds
9. **Use assertions properly** - Choose the right assertion for each case
10. **Document complex tests** - Add comments explaining test logic

---

## Manual Testing Checklist

### Installation
- [ ] Install plugin via WordPress admin
- [ ] Install plugin via FTP
- [ ] Activate plugin
- [ ] Verify no PHP errors
- [ ] Verify no JavaScript errors

### Configuration
- [ ] Enter Cloudflare Turnstile keys
- [ ] Verify keys using built-in verification
- [ ] Configure form toggles
- [ ] Test settings save
- [ ] Test settings export/import

### Form Testing
- [ ] Test each enabled form type
- [ ] Verify Turnstile widget appears
- [ ] Complete challenge and submit form
- [ ] Test form rejection without challenge
- [ ] Test form rejection with invalid token

### Edge Cases
- [ ] Test with invalid API keys
- [ ] Test with network disconnected
- [ ] Test with multiple forms on page
- [ ] Test widget refresh functionality
- [ ] Test with ad blockers enabled

---

## Resources

### Documentation
- [Codeception Documentation](https://codeception.com/docs)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [PHPStan Documentation](https://phpstan.org/)
- [ESLint Documentation](https://eslint.org/)
- [Stylelint Documentation](https://stylelint.io/)

### Testing Tools
- [WordPress Plugin Check](https://wordpress.org/plugins/plugin-check/)
- [WP Browser](https://wpbrowser.wptestkit.dev/)
- [Selenium WebDriver](https://www.selenium.dev/documentation/webdriver/)
- [ChromeDriver](https://chromedriver.chromium.org/)

### WordPress Testing
- [WordPress Plugin Handbook - Testing](https://developer.wordpress.org/plugins/testing/)
- [WordPress Core Test Suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [WP-CLI Testing](https://make.wordpress.org/cli/handbook/plugin-unit-tests/)

---

## Need Help?

If you encounter issues with testing:

1. Check this documentation
2. Review test configuration files
3. Check GitHub issues
4. Ask in WordPress support forums
5. Review Codeception documentation

---

**Last Updated:** December 20, 2024  
**Version:** 2.0  
**Maintained by:** SmartCT Team
