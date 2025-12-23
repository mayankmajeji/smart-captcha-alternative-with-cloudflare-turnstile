# TurnstileWP Plugin Documentation

## Overview

TurnstileWP is a WordPress plugin that integrates Cloudflare Turnstile CAPTCHA protection with WordPress forms. It provides lightweight and privacy-first protection for login, registration, password reset, comments, and WooCommerce forms.

**Plugin Details:**

-   Version: 1.0.0
-   Requires WordPress: 5.8+
-   Requires PHP: 7.4+
-   Author: Mayank Majeji
-   License: GPL v2 or later

## Plugin Architecture

### Main Files Structure

```
turnstilewp/
├── turnstilewp.php                 - Main plugin file
├── includes/                       - Core plugin classes
│   ├── class-init.php             - Plugin initialization
│   ├── class-loader.php           - Autoloader
│   ├── class-settings.php         - Settings management
│   ├── class-verify.php           - Token verification
│   ├── class-ajax-handlers.php    - AJAX functionality
│   ├── functions-common.php       - Common utility functions
│   └── settings/                  - Settings framework
├── integrations/                  - Form integrations
│   ├── class-turnstile.php       - Main Turnstile widget
│   ├── class-core-wp.php         - WordPress core forms
│   └── class-woocommerce.php     - WooCommerce integration
├── admin/                         - Admin interface
├── templates/                     - Template files
└── assets/                        - CSS/JS assets
```

## Core Functions Documentation

### 1. Main Plugin File (`turnstilewp.php`)

#### Global Functions

##### `turnstilewp_init()`

-   **Purpose**: Initialize the plugin after WordPress loads
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Loads text domain, initializes the main plugin class as singleton
-   **Hook**: `plugins_loaded`

##### Anonymous Activation Hook Function

-   **Purpose**: Handle plugin activation
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Requires the Init class and calls the activate method

##### Anonymous Deactivation Hook Function

-   **Purpose**: Handle plugin deactivation
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Requires the Init class and calls the deactivate method

#### Global Constants

-   `TURNSTILEWP_VERSION`: Plugin version (1.0.0)
-   `TURNSTILEWP_PLUGIN_DIR`: Plugin directory path
-   `TURNSTILEWP_PLUGIN_URL`: Plugin URL
-   `TURNSTILEWP_PLUGIN_BASENAME`: Plugin basename

---

### 2. Init Class (`includes/class-init.php`)

#### Class Properties

-   `$settings`: Settings instance
-   `$verify`: Verify instance
-   `$ajax_handlers`: AJAX handlers instance
-   `$admin_hooks_registered`: Static flag to prevent duplicate admin hooks
-   `$instance`: Singleton instance

#### Methods

##### `get_instance(): Init`

-   **Purpose**: Get singleton instance of the Init class
-   **Parameters**: None
-   **Returns**: Init instance
-   **Description**: Implements singleton pattern for plugin initialization

##### `init(): void`

-   **Purpose**: Initialize the plugin components
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Loads dependencies, initializes components, sets up integrations and hooks

##### `load_dependencies(): void`

-   **Purpose**: Load required plugin dependencies
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes common functions file

##### `init_hooks(): void`

-   **Purpose**: Initialize WordPress hooks
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Sets up admin and frontend hooks for the plugin

##### `enqueue_admin_assets(): void`

-   **Purpose**: Enqueue admin-specific CSS and JavaScript
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Loads admin styles, Turnstile API script, and admin settings JavaScript on plugin pages

##### `enqueue_frontend_assets(): void`

-   **Purpose**: Enqueue frontend CSS and JavaScript
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Loads Turnstile API script and frontend styles when needed

##### `should_load_turnstile(): bool`

-   **Purpose**: Determine if Turnstile should be loaded on current page
-   **Parameters**: None
-   **Returns**: bool - Whether to load Turnstile
-   **Description**: Checks user login status, page type, and settings to determine if Turnstile is needed

##### `activate(): void`

-   **Purpose**: Handle plugin activation tasks
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Initializes settings and adds default options

##### `deactivate(): void`

-   **Purpose**: Handle plugin deactivation tasks
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Placeholder for cleanup tasks

##### `verify_keys_ajax(): void`

-   **Purpose**: Handle AJAX request for key verification
-   **Parameters**: None (uses $\_POST data)
-   **Returns**: void (outputs JSON)
-   **Description**: Verifies Cloudflare API keys via AJAX

##### `remove_keys_ajax(): void`

-   **Purpose**: Handle AJAX request for removing API keys
-   **Parameters**: None (uses $\_POST data)
-   **Returns**: void (outputs JSON)
-   **Description**: Removes stored API keys and verification status

##### `get_client_ip(): string`

-   **Purpose**: Get client IP address
-   **Parameters**: None
-   **Returns**: string - Client IP address
-   **Description**: Extracts client IP from various server headers

##### `init_integrations(): void`

-   **Purpose**: Initialize form integrations
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Creates instances of Core_WP and WooCommerce integration classes

---

### 3. Loader Class (`includes/class-loader.php`)

#### Static Methods

##### `register(): void`

-   **Purpose**: Register the autoloader and load integrations
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Registers SPL autoloader and loads all integration files

##### `autoload(string $class): void`

-   **Purpose**: Autoload classes in the TurnstileWP namespace
-   **Parameters**:
    -   `$class`: Full class name including namespace
-   **Returns**: void
-   **Description**: Converts class names to file paths and loads class files

##### `load_integrations(): void`

-   **Purpose**: Load all integration class files
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Automatically includes all class files in the integrations directory

---

### 4. Settings Class (`includes/class-settings.php`)

#### Class Properties

-   `OPTION_NAME`: WordPress option name ('turnstilewp_settings')
-   `$defaults`: Array of default setting values
-   `$fields`: Centralized settings fields array

#### Methods

##### `__construct()`

-   **Purpose**: Initialize settings class
-   **Parameters**: None
-   **Description**: Adds default options and registers centralized fields

##### `get_settings(): array`

-   **Purpose**: Get all plugin settings
-   **Parameters**: None
-   **Returns**: array - All plugin settings merged with defaults
-   **Description**: Retrieves settings from WordPress options table

##### `get_option(string $key, $default = null)`

-   **Purpose**: Get a specific setting value
-   **Parameters**:
    -   `$key`: Setting key name
    -   `$default`: Default value if not found
-   **Returns**: mixed - Setting value or default
-   **Description**: Retrieves individual setting value

##### `add_default_options(): void`

-   **Purpose**: Add default options to database
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Creates initial settings in WordPress options table

##### `register_settings(): void`

-   **Purpose**: Register settings with WordPress Settings API
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Registers the main settings group with sanitization callback

##### `sanitize_settings(array $input): array`

-   **Purpose**: Sanitize settings input
-   **Parameters**:
    -   `$input`: Raw input array from form submission
-   **Returns**: array - Sanitized settings array
-   **Description**: Validates and sanitizes all setting values based on field types

##### `add_admin_menu(): void`

-   **Purpose**: Add admin menu pages
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Creates main menu and submenu pages for the plugin

##### `render_dashboard_page(): void`

-   **Purpose**: Render the dashboard page
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes dashboard template file

##### `render_integrations_page(): void`

-   **Purpose**: Render the integrations page
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes integrations template file

##### `render_settings_main_page(): void`

-   **Purpose**: Render the main settings page
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes settings template file

##### `render_tools_page(): void`

-   **Purpose**: Render the tools page
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes tools template file

##### `render_faqs_page(): void`

-   **Purpose**: Render the FAQs page
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Includes FAQs template file

##### `register_centralized_fields(): void`

-   **Purpose**: Register all settings fields in centralized system
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Sets up the organized field structure for settings

##### `organize_fields(array $fields): array`

-   **Purpose**: Organize fields by tabs and sections
-   **Parameters**:
    -   `$fields`: Array of field definitions
-   **Returns**: array - Organized field structure
-   **Description**: Groups fields into hierarchical structure for rendering

##### `get_fields_structure(): array`

-   **Purpose**: Get the organized fields structure
-   **Parameters**: None
-   **Returns**: array - Organized fields by tab and section
-   **Description**: Returns the complete field organization for rendering

---

### 5. Verify Class (`includes/class-verify.php`)

#### Class Properties

-   `$settings`: Settings instance

#### Methods

##### `__construct()`

-   **Purpose**: Initialize verification class
-   **Parameters**: None
-   **Description**: Creates Settings instance for accessing configuration

##### `verify_token(string $token, ?string $custom_secret_key = null): bool`

-   **Purpose**: Verify Turnstile token with Cloudflare API
-   **Parameters**:
    -   `$token`: Turnstile response token
    -   `$custom_secret_key`: Optional custom secret key for admin verification
-   **Returns**: bool - Whether verification was successful
-   **Description**: Sends verification request to Cloudflare Turnstile API

##### `get_client_ip(): string`

-   **Purpose**: Get client IP address for verification
-   **Parameters**: None
-   **Returns**: string - Client IP address
-   **Description**: Extracts client IP from various server headers

##### `log_error(string $message): void`

-   **Purpose**: Log error messages when debug mode is enabled
-   **Parameters**:
    -   `$message`: Error message to log
-   **Returns**: void
-   **Description**: Writes error messages to WordPress error log if debug mode is active

---

### 6. Ajax Handlers Class (`includes/class-ajax-handlers.php`)

#### Methods

##### `__construct()`

-   **Purpose**: Initialize AJAX handlers
-   **Parameters**: None
-   **Description**: Registers AJAX action hooks

##### `export_settings(): void`

-   **Purpose**: Export plugin settings as JSON file
-   **Parameters**: None (uses $\_GET data)
-   **Returns**: void (outputs file download)
-   **Description**: Verifies permissions and nonce, then exports settings as downloadable JSON

---

### 7. Common Functions (`includes/functions-common.php`)

#### Utility Functions

##### `is_login(): bool`

-   **Purpose**: Check if current page is login page
-   **Parameters**: None
-   **Returns**: bool - Whether on login page
-   **Description**: Determines if user is on wp-login.php

##### `is_registration_page(): bool`

-   **Purpose**: Check if current page is registration page
-   **Parameters**: None
-   **Returns**: bool - Whether on registration page
-   **Description**: Checks for wp-login.php with register action

##### `is_lost_password_page(): bool`

-   **Purpose**: Check if current page is lost password page
-   **Parameters**: None
-   **Returns**: bool - Whether on lost password page
-   **Description**: Checks for wp-login.php with lostpassword action

##### `is_comment_form_page(): bool`

-   **Purpose**: Check if current page has comment form
-   **Parameters**: None
-   **Returns**: bool - Whether page has comment form
-   **Description**: Determines if page is singular and comments are open

---

### 8. Turnstile Integration Class (`integrations/class-turnstile.php`)

#### Class Properties

-   `$settings`: Settings instance

#### Methods

##### `__construct()`

-   **Purpose**: Initialize Turnstile integration
-   **Parameters**: None
-   **Description**: Creates Settings instance and hooks into WordPress actions

##### `enqueue_script(): void`

-   **Purpose**: Enqueue Turnstile JavaScript API
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Loads Cloudflare Turnstile script when site key is configured

##### `render(string $action): void`

-   **Purpose**: Render Turnstile widget
-   **Parameters**:
    -   `$action`: Action name for the widget
-   **Returns**: void
-   **Description**: Outputs Turnstile widget HTML with configured settings

##### `verify(?string $token = null): bool`

-   **Purpose**: Verify Turnstile response token
-   **Parameters**:
    -   `$token`: Optional token (uses POST data if not provided)
-   **Returns**: bool - Verification result
-   **Description**: Verifies token with Cloudflare API

##### `render_dynamic(array $args = []): void`

-   **Purpose**: Render Turnstile widget with dynamic configuration
-   **Parameters**:
    -   `$args`: Array of rendering arguments
-   **Returns**: void
-   **Description**: Renders widget with customizable attributes and hooks

---

### 9. Core WordPress Integration (`integrations/class-core-wp.php`)

#### Class Properties

-   `$settings`: Settings instance
-   `$verify`: Verify instance

#### Methods

##### `__construct()`

-   **Purpose**: Initialize WordPress core integration
-   **Parameters**: None
-   **Description**: Sets up Settings and Verify instances, initializes hooks

##### `init_hooks(): void`

-   **Purpose**: Initialize hooks for WordPress core forms
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Sets up hooks for login, registration, password reset, and comment forms

##### `render_turnstile_field(): void`

-   **Purpose**: Render Turnstile field for core WordPress forms
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Outputs Turnstile widget if keys are verified and conditions are met

##### `get_form_context(): string`

-   **Purpose**: Determine current form context
-   **Parameters**: None
-   **Returns**: string - Form context identifier
-   **Description**: Uses current filter to identify which form is being processed

##### `verify_login($user, string $username, string $password)`

-   **Purpose**: Verify Turnstile for login attempts
-   **Parameters**:
    -   `$user`: User object or WP_Error from previous callbacks
    -   `$username`: Username being verified
    -   `$password`: Password being verified
-   **Returns**: WP_User|WP_Error - User object or error
-   **Description**: Validates Turnstile token during login process

##### `verify_registration(\WP_Error $errors, string $sanitized_user_login, string $user_email): \WP_Error`

-   **Purpose**: Verify Turnstile for user registration
-   **Parameters**:
    -   `$errors`: Existing registration errors
    -   `$sanitized_user_login`: Sanitized username
    -   `$user_email`: User email address
-   **Returns**: WP_Error - Updated errors object
-   **Description**: Adds Turnstile verification to registration process

##### `verify_lost_password(\WP_Error $errors): \WP_Error`

-   **Purpose**: Verify Turnstile for password reset requests
-   **Parameters**:
    -   `$errors`: Existing password reset errors
-   **Returns**: WP_Error - Updated errors object
-   **Description**: Validates Turnstile token for password reset

##### `verify_comment(array $commentdata): array`

-   **Purpose**: Verify Turnstile for comment submissions
-   **Parameters**:
    -   `$commentdata`: Comment data array
-   **Returns**: array - Comment data (unchanged if valid)
-   **Description**: Validates Turnstile token before processing comments

##### `verify_token(): bool`

-   **Purpose**: Verify Turnstile token from POST data
-   **Parameters**: None
-   **Returns**: bool - Verification result
-   **Description**: Helper method to verify token using Verify class

##### `should_show_turnstile(): bool`

-   **Purpose**: Check if Turnstile should be displayed
-   **Parameters**: None
-   **Returns**: bool - Whether to show Turnstile
-   **Description**: Considers user login status and settings to determine display

---

### 10. WooCommerce Integration (`integrations/class-woocommerce.php`)

#### Class Properties

-   `$settings`: Settings instance

#### Methods

##### `__construct()`

-   **Purpose**: Initialize WooCommerce integration
-   **Parameters**: None
-   **Description**: Sets up Settings instance, registers fields, and initializes hooks

##### `register_settings_fields(): void`

-   **Purpose**: Register WooCommerce-specific settings fields
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Adds WooCommerce form integration settings to centralized system

##### `init_hooks(): void`

-   **Purpose**: Initialize WooCommerce form hooks
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Sets up hooks for various WooCommerce forms based on settings

##### `add_checkout_hooks(string $location): void`

-   **Purpose**: Add checkout hooks based on selected location
-   **Parameters**:
    -   `$location`: Where to place the Turnstile widget on checkout
-   **Returns**: void
-   **Description**: Hooks into appropriate WooCommerce checkout actions

##### `render_turnstile(): void`

-   **Purpose**: Render Turnstile widget for WooCommerce forms
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Outputs Turnstile widget if keys are verified

##### `verify_token(): bool`

-   **Purpose**: Verify Turnstile token
-   **Parameters**: None
-   **Returns**: bool - Verification result
-   **Description**: Helper method to verify token using Verify class

##### `verify_turnstile($validation_error, $username, $password): \WP_Error`

-   **Purpose**: Verify Turnstile for WooCommerce login
-   **Parameters**:
    -   `$validation_error`: Existing validation errors
    -   `$username`: Login username
    -   `$password`: Login password
-   **Returns**: WP_Error - Updated validation errors
-   **Description**: Adds Turnstile verification to WooCommerce login

##### `verify_turnstile_registration($errors, $username, $email): \WP_Error`

-   **Purpose**: Verify Turnstile for WooCommerce registration
-   **Parameters**:
    -   `$errors`: Existing registration errors
    -   `$username`: Registration username
    -   `$email`: Registration email
-   **Returns**: WP_Error - Updated errors
-   **Description**: Validates Turnstile for WooCommerce user registration

##### `verify_turnstile_reset_password($errors, $user_data): \WP_Error`

-   **Purpose**: Verify Turnstile for WooCommerce password reset
-   **Parameters**:
    -   `$errors`: Existing reset errors
    -   `$user_data`: User data for reset
-   **Returns**: WP_Error - Updated errors
-   **Description**: Validates Turnstile for password reset requests

##### `verify_turnstile_checkout(): void`

-   **Purpose**: Verify Turnstile for WooCommerce checkout
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Validates Turnstile during checkout process, adds error if failed

##### `verify_turnstile_pay_order(): void`

-   **Purpose**: Verify Turnstile for pay-for-order process
-   **Parameters**: None
-   **Returns**: void
-   **Description**: Validates Turnstile when paying for existing orders

---

### 11. Field Renderer (`includes/settings/field-renderer.php`)

#### Functions

##### `render_setting_field($field, $value)`

-   **Purpose**: Render individual settings field
-   **Parameters**:
    -   `$field`: Field configuration array
    -   `$value`: Current field value
-   **Returns**: void (outputs HTML)
-   **Description**: Renders form fields based on type (text, checkbox, select, etc.)

##### `render_setting_fields_grouped($fields, $values)`

-   **Purpose**: Render grouped settings fields
-   **Parameters**:
    -   `$fields`: Array of field configurations
    -   `$values`: Array of current values
-   **Returns**: void (outputs HTML)
-   **Description**: Renders fields in groups with appropriate titles and containers

---

## Settings Field Types

The plugin supports various field types for the settings system:

### Field Types

1. **text** - Standard text input
2. **password** - Password input (masked)
3. **email** - Email input with validation
4. **url** - URL input with validation
5. **number** - Numeric input
6. **checkbox** - Boolean checkbox with toggle styling
7. **select** - Dropdown selection
8. **textarea** - Multi-line text input
9. **content** - Static content display

### Field Configuration

Each field can have the following properties:

-   `field_id`: Unique identifier
-   `label`: Display label
-   `description`: Help text
-   `type`: Field type
-   `default`: Default value
-   `options`: Options for select fields
-   `tab`: Settings tab
-   `section`: Settings section
-   `group`: Field group
-   `priority`: Display order
-   `sanitize_callback`: Custom sanitization function

---

## Hooks and Filters

### Actions

-   `turnstilewp_before_field`: Before widget rendering
-   `turnstilewp_after_field`: After widget rendering
-   Various WordPress core hooks for form integration

### Filters

-   `turnstilewp_settings`: Add/modify settings fields
-   `turnstilewp_widget_disable`: Conditionally disable widget
-   `turnstilewp_should_show`: Control widget display

---

## Security Features

### Token Verification

-   Server-side verification with Cloudflare API
-   IP address validation
-   Nonce verification for admin actions
-   Input sanitization and validation

### Data Protection

-   Sensitive data handling (secret keys)
-   Debug logging controls
-   Secure AJAX endpoints
-   WordPress capability checks

---

## Error Handling

### Debug Mode

-   Configurable debug logging
-   Error message customization
-   Detailed verification logs
-   Admin notification system

### Fallback Behavior

-   Graceful degradation when API is unavailable
-   User-friendly error messages
-   Automatic retry mechanisms
-   Admin tools for troubleshooting

---

## Extensibility

### Developer Hooks

The plugin provides numerous hooks for customization:

-   Field rendering hooks
-   Verification process hooks
-   Settings modification filters
-   Widget display controls

### Integration Framework

-   Modular integration system
-   Easy addition of new form types
-   Standardized verification process
-   Consistent widget rendering

---

## Database Schema

### Options Table

-   `turnstilewp_settings`: Main plugin settings
-   `turnstilewp_keys_verified`: Key verification status

### Transients

-   `turnstilewp_debug_log`: Debug logging data

---

## Settings Reference

### Overview

The TurnstileWP plugin uses a standardized naming convention with the `tswp_` prefix for all settings keys.

### Key Naming Pattern

All settings keys use the `tswp_` prefix for consistency and to prevent naming collisions:

```php
// Always use the prefixed pattern
$site_key = $settings->get_option('tswp_site_key');
$theme = $settings->get_option('tswp_theme', 'auto');
$enabled = $settings->get_option('tswp_enable_login', true);
```

**Why the Prefix?**
1. **Prevents Collisions**: Avoids naming conflicts with other plugins
2. **Clear Ownership**: Immediately identifies settings as belonging to this plugin
3. **Consistency**: All settings follow the same pattern
4. **WordPress Standards**: Follows WordPress best practices for plugin options

### Storage

Settings are stored in the WordPress options table under the key `turnstilewp_settings` as a serialized array:

```php
// Database storage example
[
    'tswp_site_key' => 'your-site-key',
    'tswp_secret_key' => 'your-secret-key',
    'tswp_theme' => 'auto',
    'tswp_enable_login' => true,
]
```

### Priority Order

When retrieving options, the following priority is used:

1. **Constants** (wp-config.php overrides):
   - `TURNSTILEWP_SITE_KEY`
   - `TURNSTILEWP_SECRET_KEY`

2. **Database Values** (from `turnstilewp_settings` option)

### Constants Support

You can override settings via wp-config.php:

```php
// wp-config.php
define('TURNSTILEWP_SITE_KEY', 'your-site-key');
define('TURNSTILEWP_SECRET_KEY', 'your-secret-key');
```

When constants are defined, they take precedence over database values.

### Available Settings

#### Core Settings

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_site_key` | string | '' | Cloudflare Turnstile site key |
| `tswp_secret_key` | string | '' | Cloudflare Turnstile secret key |
| `tswp_keys_verified` | boolean | false | Whether keys have been verified |

#### Widget Appearance

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_theme` | string | 'auto' | Widget theme: 'auto', 'light', 'dark' |
| `tswp_widget_size` | string | 'normal' | Widget size: 'normal', 'flexible', 'compact' |
| `tswp_appearance_mode` | string | 'always' | Appearance mode: 'always', 'interaction_only' |
| `tswp_language` | string | 'auto' | Language code (e.g., 'en-us', 'fr-fr') |

#### Core WordPress Features

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_enable_login` | boolean | true | Enable on login form |
| `tswp_enable_register` | boolean | true | Enable on registration form |
| `tswp_enable_lost_password` | boolean | true | Enable on lost password form |
| `tswp_enable_comments` | boolean | true | Enable on comment forms |
| `tswp_show_for_logged_in` | boolean | false | Show for logged-in users |

#### Error Handling

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_custom_error_message` | string | '' | Custom error message for failed verification |
| `tswp_extra_failure_message` | string | '' | Additional instructions on failure |
| `tswp_debug_mode` | boolean | false | Enable debug logging |

#### Script Options

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_defer_script` | boolean | false | Defer Turnstile script loading |

#### WooCommerce Integration

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `tswp_woo_login` | boolean | false | Enable on WooCommerce login |
| `tswp_woo_register` | boolean | false | Enable on WooCommerce registration |
| `tswp_woo_reset_password` | boolean | false | Enable on WooCommerce password reset |
| `tswp_woo_checkout` | boolean | false | Enable on WooCommerce checkout |
| `tswp_woo_checkout_guest_only` | boolean | false | Only for guest checkout |
| `tswp_woo_checkout_location` | string | 'before_payment' | Checkout position |
| `tswp_woo_pay_for_order` | boolean | false | Enable on pay for order page |

#### Form Plugin Integrations

Each form plugin follows the pattern `tswp_{plugin}_enable` and `tswp_{plugin}_position`:

- **Contact Form 7**: `tswp_cf7_enable`, `tswp_cf7_position`
- **WPForms**: `tswp_wpforms_enable`, `tswp_wpforms_position`
- **Fluent Forms**: `tswp_fluent_enable`, `tswp_fluent_position`
- **Ninja Forms**: `tswp_nf_enable`
- **Formidable Forms**: `tswp_formidable_enable`, `tswp_formidable_position`
- **Forminator**: `tswp_forminator_enable`, `tswp_forminator_position`
- **Everest Forms**: `tswp_everest_forms_enable`, `tswp_everest_forms_position`
- **SureForms**: `tswp_sureforms_enable`, `tswp_sureforms_position`

### Developer Guidelines

#### Retrieving Settings

```php
// Get Settings instance
$settings = new \TurnstileWP\Settings();

// Retrieve a setting
$value = $settings->get_option('tswp_site_key');

// With default value
$theme = $settings->get_option('tswp_theme', 'auto');

// Constants take precedence
define('TURNSTILEWP_SITE_KEY', 'constant-value');
$value = $settings->get_option('tswp_site_key'); // Returns 'constant-value'
```

#### Adding Custom Settings

When hooking into the settings system via `turnstilewp_settings` filter:

```php
add_filter('turnstilewp_settings', function($fields) {
    $fields[] = array(
        'field_id' => 'tswp_my_custom_setting',  // Always use tswp_ prefix
        'label' => __('My Custom Setting', 'turnstilewp'),
        'description' => __('Description of the setting', 'turnstilewp'),
        'type' => 'text',  // text, checkbox, select, etc.
        'tab' => 'my_integration',
        'section' => 'my_section',
        'default' => '',
        'priority' => 10,
    );
    return $fields;
});
```

**Note:** The system automatically normalizes field IDs. If you pass a field ID without the `tswp_` prefix, it will be automatically added.

#### Supported Field Types

1. **text** - Standard text input
2. **password** - Password input (masked)
3. **email** - Email input with validation
4. **url** - URL input with validation
5. **number** - Numeric input
6. **checkbox** - Boolean checkbox with toggle styling
7. **select** - Dropdown selection
8. **multiselect** - Multiple selection dropdown
9. **textarea** - Multi-line text input
10. **content** - Static content display

### Key Verification

When site/secret keys are changed or emptied (and not overridden by constants), the `turnstilewp_keys_verified` option is automatically reset to `0`.

---

## Utility Functions

### `\TurnstileWP\get_client_ip(): string`

Get client IP address with proper fallback and sanitization.

**Purpose**: Determines the client's IP address by checking multiple SERVER variables.

**Returns**: Sanitized client IP address, or empty string if not available.

**Description**: 
- Checks `HTTP_CLIENT_IP` first (highest priority)
- Then checks `HTTP_X_FORWARDED_FOR` (handles proxied requests)
- Falls back to `REMOTE_ADDR` (direct connections)
- Handles comma-separated IP lists from proxies
- Sanitizes all values using WordPress functions
- Provides `turnstilewp_client_ip` filter for custom logic

**Usage**:
```php
// Get client IP anywhere in the plugin
$ip = \TurnstileWP\get_client_ip();

// Custom IP detection via filter
add_filter('turnstilewp_client_ip', function($ip) {
    // Custom logic here
    return $ip;
});
```

**Since**: 1.0.0

---

This documentation covers all major functions, classes, settings, and utilities in the TurnstileWP plugin. Each function is documented with its purpose, parameters, return values, and functionality to provide a complete reference for developers and users working with this plugin.
