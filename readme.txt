=== Smart CAPTCHA Alternative with Cloudflare Turnstile ===
Contributors: mayankmajeji, sppramodh
Tags: captcha, cloudflare, turnstile, security, woocommerce
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smart CAPTCHA Alternative with Cloudflare Turnstile for WordPress and WooCommerce. Fast, privacy‑first bot protection for core forms and checkout.

== Description ==

Smart CAPTCHA Alternative integrates [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/) into WordPress core forms and WooCommerce:

* Login, Registration, Lost Password, Comments
* WooCommerce: Login, Registration, Reset Password, Checkout, Pay for Order
* Lightweight and privacy‑first — no images or puzzles
* Server‑side verification against Cloudflare API
* Only loads when needed

== Features ==

* Easy key setup (Site/Secret)
* Toggle integrations per form
* Placement options for WooCommerce checkout
* Theme (Auto/Light/Dark) and size controls
* Optional script defer
* Debug logging for troubleshooting

== Installation ==

1. Upload the `smart-cloudflare-turnstile` folder to `/wp-content/plugins/`, or install via Plugins → Add New.
2. Activate "Smart CAPTCHA Alternative with Cloudflare Turnstile".
3. Go to Settings → Smart CAPTCHA Alternative and enter your Cloudflare Turnstile Site & Secret keys.

== Frequently Asked Questions ==

= Do I need a Cloudflare account? =
Yes. Generate Turnstile Site and Secret keys from your Cloudflare dashboard and paste them into the plugin settings.

= Does it work with WooCommerce? =
Yes. Turnstile is available for WooCommerce Login, Registration, Reset Password, Checkout, and Pay for Order pages. Placement for checkout is configurable.

= Will it slow down my site? =
The script is loaded only when required and can be deferred. The plugin aims to keep a minimal footprint.

= Where can I find full documentation? =
Visit the [plugin support forum](https://wordpress.org/support/plugin/smart-captcha-alternative-with-cloudflare-turnstile/) or check the [GitHub repository](https://github.com/mayankmajeji/smart-captcha-alternative-with-cloudflare-turnstile) for documentation and examples.


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

= 1.0.0 =
Initial release.
