=== Smart CAPTCHA Alternative with Cloudflare Turnstile ===
Contributors: mayankmajeji, sppramodh
Tags: captcha, cloudflare, turnstile, security, woocommerce
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Protect WordPress forms from spam using Cloudflare Turnstile. A privacy-friendly CAPTCHA alternative.

== Description ==

**Smart CAPTCHA Alternative with Cloudflare Turnstile** helps you block spam and bots without hurting user experience.

Instead of frustrating image puzzles or invasive tracking, this plugin integrates **Cloudflare Turnstile**, a modern CAPTCHA alternative that silently verifies real users while respecting privacy.

The plugin is lightweight, loads only when required, and performs server-side verification against Cloudflare’s API.

== Supported Forms ==

**WordPress:**
* Login Form
* Registration Form
* Password Reset Form
* Comments Form

**WooCommerce:**
* Checkout
* Pay For Order
* Login Form
* Registration Form
* Password Reset Form

**Form Plugins:**
* Contact Form 7
* WPForms
* Fluent Forms
* Formidable Forms
* Ninja Forms
* Forminator Forms
* Everest Forms
* SureForms
* Kadence Forms

**Other Integrations:**
* MailPoet Forms
* BuddyPress Registration Form
* bbPress Create Topic & Reply Forms

**Why Choose This Plugin:**
* Zero user friction — no puzzles or image challenges
* Privacy-focused — respects user privacy while providing security
* Performance optimized — scripts load only when forms are present
* Currently free — no premium version, no hidden costs, no tracking
* Server-side validation — secure verification through Cloudflare's API

== Features ==

The plugin includes several features and options:

* **Easy Setup**: Simple key configuration (Site Key & Secret Key)
* **Per-Form Control**: Toggle Turnstile on/off for each integration individually
* **WooCommerce Options**: Configurable placement options for checkout forms
* **Customization**: 
  * Theme selection (Auto/Light/Dark)
  * Widget size controls (Normal/Compact/Flexible)
  * Language settings
  * Appearance mode (Always visible or interaction-only)
* **Performance**: Optional script defer for improved page load times
* **Debugging**: Debug logging to help troubleshoot form submission issues

== Installation ==

1. Upload the `smart-cloudflare-turnstile` folder to `/wp-content/plugins/`, or install via Plugins → Add New.
2. Activate "Smart CAPTCHA Alternative with Cloudflare Turnstile".
3. Go to Settings → Smart CAPTCHA Alternative and enter your Cloudflare Turnstile Site & Secret keys.

== Getting Started ==

Setting up Cloudflare Turnstile protection is straightforward:

1. Create your Turnstile keys in the Cloudflare dashboard (Site Key and Secret Key)
2. Navigate to Dashboard → Smart Cloudflare Turnstile in your WordPress admin
3. Enter your keys and choose which forms to protect
4. Save your settings — Turnstile will now appear on your selected forms

That's it! Your forms are now protected from spam and bots.

Need help? Check our documentation or visit the support forum for detailed guides.

== Frequently Asked Questions ==

= What is Cloudflare Turnstile? =
Cloudflare Turnstile is a modern bot protection solution that verifies real users without showing puzzles or challenges. It works invisibly in the background, providing security without disrupting the user experience. Unlike traditional CAPTCHAs, Turnstile is designed to be privacy-friendly and user-friendly.

Learn more: https://www.cloudflare.com/products/turnstile/

= Is this plugin free? =
The plugin is currently free to use with no premium version or hidden costs. There's no data tracking or analytics. Cloudflare Turnstile itself is also a free service provided by Cloudflare.

= Do I need a Cloudflare account? =
Yes. Generate Turnstile Site and Secret keys from your Cloudflare dashboard and paste them into the plugin settings.

= Does it work with WooCommerce? =
Yes. Turnstile is available for WooCommerce Login, Registration, Reset Password, Checkout, and Pay for Order pages. Placement for checkout is configurable.

= What other plugins are supported? =
The plugin supports many popular form builders (Contact Form 7, WPForms, Fluent Forms, Formidable Forms, Ninja Forms, Forminator, Everest Forms, SureForms, Kadence Forms), community plugins (bbPress, BuddyPress), and newsletter plugins (MailPoet). Each integration can be enabled or disabled individually in the plugin settings.

= Will this affect my site's performance? =
Not at all. The plugin is designed with performance in mind. Turnstile scripts only load on pages that contain protected forms, and you can enable script deferring for even better performance. The plugin has minimal impact on page load times.

= Where can I find full documentation? =
Visit the [plugin support forum](https://wordpress.org/support/plugin/smart-captcha-alternative-with-cloudflare-turnstile/) or check the [GitHub repository](https://github.com/mayankmajeji/smart-captcha-alternative-with-cloudflare-turnstile) for documentation and examples.

== Plugin Languages ==

The plugin is currently available in English. If you'd like to help translate the plugin into your language, please visit our [translation page](https://translate.wordpress.org/projects/wp-plugins/smart-captcha-alternative-with-cloudflare-turnstile/).

== Other Information ==

* For help & suggestions, please create a support topic in our [support forum](https://wordpress.org/support/plugin/smart-captcha-alternative-with-cloudflare-turnstile/)
* Follow the developer [@mayankmajeji](https://x.com/mayankmajeji)
* [View on GitHub](https://github.com/mayankmajeji/smart-captcha-alternative-with-cloudflare-turnstile)

== Support The Plugin ==

Special thanks to all contributors who help support the continued development of this plugin.

The plugin is currently free to use. If you find it useful and would like to support its continued development, maintenance, and support, you can make a donation. Your support is greatly appreciated and helps keep the plugin actively maintained.

[Make a donation](https://buymeacoffee.com/mayankmajeji)

== Trademark Notice ==

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.

This plugin is not affiliated with, endorsed by, or sponsored by Cloudflare, Inc. Cloudflare® and Turnstile® are trademarks of Cloudflare, Inc.


== Third Party Services ==

This plugin connects to Cloudflare Turnstile service to provide CAPTCHA verification.

**What data is transmitted:**
- Form submission tokens for verification
- IP addresses for bot detection
- Browser fingerprints for security analysis

**When data is sent:**
- Only when users submit forms with Turnstile protection enabled
- Data is sent securely via HTTPS to Cloudflare's servers

**External Services:**
- Cloudflare Turnstile API: https://developers.cloudflare.com/turnstile/
- Cloudflare Terms of Service: https://www.cloudflare.com/terms/
- Cloudflare Privacy Policy: https://www.cloudflare.com/privacypolicy/

== Screenshots ==

1. General settings screen with key configuration
2. WooCommerce integration options
3. Example widget rendering on a login form

== Changelog ==

= 1.1.2 =
* Fixed readme.txt short description.

= 1.1.1 =
* Fixed readme.txt short description.

= 1.1.0 =
* Added bbPress integration (Topic Creation and Reply forms)
* Added BuddyPress integration (User Registration form)
* Added MailPoet integration (Subscription forms)
* Added Kadence Forms integration (Advanced Form blocks)
* Improved security: Fixed input sanitization and output escaping

= 1.0.0 =
Initial release.
