# Contributing to SmartCT

Thank you for your interest in contributing to SmartCT! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Making Changes](#making-changes)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Adding Integrations](#adding-integrations)
- [Reporting Issues](#reporting-issues)

---

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful, inclusive, and considerate in all interactions.

### Our Standards

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

---

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:

- PHP 7.4 or higher
- Composer
- Node.js 18 or higher
- MySQL/MariaDB
- WordPress 5.8 or higher
- Git

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:

```bash
git clone https://github.com/YOUR-USERNAME/turnstilewp.git
cd turnstilewp
```

3. Add the upstream repository:

```bash
git remote add upstream https://github.com/mayankmajeji/turnstilewp.git
```

---

## Development Setup

### Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build Codeception
vendor/bin/codecept build

# Compile assets
npm run gulp:styles
```

### Development Tools

- **Gulp** - For asset compilation
- **Codeception** - For testing
- **PHPCS** - For PHP coding standards
- **PHPStan** - For static analysis
- **ESLint** - For JavaScript linting
- **Stylelint** - For CSS/SCSS linting

---

## Coding Standards

### PHP

We follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

**Key Points:**
- Use tabs for indentation
- Use single quotes for strings (unless interpolating)
- Always escape output
- Always sanitize input
- Use type hints where possible
- Document all functions with PHPDoc

**Check standards:**
```bash
composer run lint
```

**Auto-fix issues:**
```bash
composer run format
```

### JavaScript

We follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/).

**Check standards:**
```bash
npm run lint:js
```

**Auto-fix issues:**
```bash
npm run fix:js
```

### CSS/SCSS

We follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/).

**Check standards:**
```bash
npm run lint:css
```

**Auto-fix issues:**
```bash
npm run fix:css
```

### Settings Keys

**Always use the `tswp_` prefix for all settings:**

```php
// ✅ CORRECT
$site_key = $settings->get_option('tswp_site_key');
$enabled = $settings->get_option('tswp_enable_login', true);

// ❌ WRONG
$site_key = $settings->get_option('site_key');
```

### IP Detection

**Always use the centralized function:**

```php
// ✅ CORRECT
$ip = \SmartCT\get_client_ip();

// ❌ WRONG
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
```

---

## Making Changes

### Branch Naming

Create a descriptive branch for your changes:

- `feature/add-gravity-forms-integration`
- `fix/checkout-validation-issue`
- `docs/update-api-reference`
- `refactor/simplify-settings-class`

### Commit Messages

Write clear, descriptive commit messages:

```
Add Gravity Forms integration

- Add integration class for Gravity Forms
- Implement widget rendering
- Add verification hooks
- Add settings fields
- Update documentation
```

**Format:**
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- First line should be 50 characters or less
- Reference issues and pull requests when relevant

### Keep Changes Focused

- One feature or fix per pull request
- Don't mix unrelated changes
- Keep pull requests small and manageable

---

## Testing

### Run All Tests

```bash
npm run test:all
```

### Run Specific Tests

```bash
# PHP coding standards
composer run test:phpcs

# PHP static analysis
composer run test:phpstan

# Unit tests
npm run test:unit

# Integration tests
npm run test:integration
```

### Write Tests

All new features should include tests:

**Unit Test Example:**
```php
<?php
namespace SmartCT\Tests\Unit;

use SmartCT\YourNewClass;
use Codeception\Test\Unit;

class YourNewClassTest extends Unit
{
    public function testYourMethod()
    {
        $instance = new YourNewClass();
        $result = $instance->yourMethod();
        $this->assertTrue($result);
    }
}
```

See [docs/TESTING.md](TESTING.md) for comprehensive testing guide.

---

## Pull Request Process

### Before Submitting

1. **Update from upstream:**
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

2. **Run all tests:**
   ```bash
   npm run test:all
   ```

3. **Check for linting errors:**
   ```bash
   npm run lint:all
   ```

4. **Update documentation** if needed

5. **Test manually** in a WordPress environment

### Submitting

1. Push your branch to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

2. Create a pull request on GitHub

3. Fill out the pull request template completely

4. Link any related issues

### Pull Request Template

Your PR should include:

- **Description** - What changes are being made and why
- **Type of Change** - Feature, bug fix, refactoring, docs, etc.
- **Testing** - How you tested the changes
- **Screenshots** - For UI changes
- **Checklist** - Confirming tests pass, docs updated, etc.

### Review Process

- At least one maintainer will review your PR
- Address any feedback or requested changes
- Once approved, a maintainer will merge your PR

---

## Adding Integrations

### Form Plugin Integration

To add a new form plugin integration:

1. **Create integration class:**

```php
<?php
// includes/integrations/forms/class-your-form.php

namespace SmartCT\Integrations;

use SmartCT\Turnstile;
use SmartCT\Settings;
use SmartCT\Verify;

class Your_Form {
    private Settings $settings;
    private Verify $verify;
    
    public function __construct() {
        // Check if form plugin is active
        if ( ! $this->is_active() ) {
            return;
        }
        
        $this->settings = new Settings();
        $this->verify = new Verify();
        
        // Register settings
        add_filter('turnstilewp_settings', array( $this, 'register_settings' ));
        
        $this->init_hooks();
    }
    
    private function is_active(): bool {
        return defined('YOUR_FORM_VERSION') || class_exists('YourFormClass');
    }
    
    public function register_settings(array $fields): array {
        $fields[] = array(
            'field_id' => 'tswp_yourform_enable',
            'label' => __('Enable on Your Form', 'turnstilewp'),
            'type' => 'checkbox',
            'tab' => 'form_plugins',
            'section' => 'your_form',
            'default' => false,
        );
        return $fields;
    }
    
    private function init_hooks(): void {
        if ( ! $this->settings->get_option('tswp_yourform_enable', false) ) {
            return;
        }
        
        // Add your hooks here
        add_action('your_form_action', array( $this, 'render_turnstile' ));
        add_filter('your_form_validate', array( $this, 'verify_turnstile' ));
    }
    
    public function render_turnstile(): void {
        $turnstile = new Turnstile();
        $turnstile->render_dynamic(array(
            'form_name' => 'your-form',
            'unique_id' => uniqid(),
        ));
    }
    
    public function verify_turnstile($result) {
        $token = $_POST['cf-turnstile-response'] ?? '';
        if ( ! $this->verify->verify_token($token) ) {
            // Add error to result
        }
        return $result;
    }
}
```

2. **Register in Init class:**

Add to `includes/class-init.php` in the `init_integrations()` method:

```php
// Your Form integration (if Your Form is active)
if ( defined('YOUR_FORM_VERSION') || class_exists('YourFormClass') ) {
    new \SmartCT\Integrations\Your_Form();
}
```

3. **Add to documentation:**

Update:
- `docs/API-REFERENCE.md` - Add integration class documentation
- `README.md` - Add to integrations list

4. **Add tests:**

Create `tests/unit/YourFormTest.php` and `tests/integration/YourFormIntegrationTest.php`

5. **Add integration image:**

Add logo to `assets/images/integrations/your-form.png` (200x200px)

### Best Practices for Integrations

- Always check if the form plugin is active
- Use consistent naming (`tswp_formname_enable`)
- Follow the existing integration patterns
- Add proper error handling
- Document all hooks and filters used
- Test with and without the form plugin active

---

## Reporting Issues

### Before Creating an Issue

1. **Search existing issues** - Your issue may already be reported
2. **Test with default WordPress theme** - Rule out theme conflicts
3. **Disable other plugins** - Check for plugin conflicts
4. **Check WordPress and PHP versions** - Ensure compatibility

### Creating an Issue

Include the following information:

- **WordPress version**
- **PHP version**
- **SmartCT version**
- **Active theme**
- **Active plugins** (list relevant ones)
- **Steps to reproduce**
- **Expected behavior**
- **Actual behavior**
- **Screenshots** (if applicable)
- **Error messages** (from debug.log)

### Issue Labels

- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Documentation improvements
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention needed
- `question` - Further information requested
- `wontfix` - Will not be worked on

---

## Documentation

### API Documentation

When adding new functions or classes, update `docs/API-REFERENCE.md`:

```markdown
##### `your_new_function(string $param): bool`

- **Purpose**: Brief description
- **Parameters**:
  - `$param`: Parameter description
- **Returns**: bool - Return value description
- **Description**: Detailed description of functionality
```

### Code Comments

Add PHPDoc blocks to all functions and classes:

```php
/**
 * Brief description
 *
 * Detailed description if needed.
 *
 * @since 1.0.0
 * @param string $param Parameter description.
 * @return bool Return value description.
 */
public function your_function( string $param ): bool {
    // Implementation
}
```

---

## Questions?

If you have questions about contributing:

1. Check existing documentation
2. Search closed issues and PRs
3. Ask in GitHub Discussions
4. Contact maintainers

---

## License

By contributing to SmartCT, you agree that your contributions will be licensed under the GPL v2 or later license.

---

**Thank you for contributing to SmartCT!** 🎉

Your contributions help make this plugin better for everyone.
