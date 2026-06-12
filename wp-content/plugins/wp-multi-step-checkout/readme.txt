=== Multi-Step Checkout for WooCommerce ===
Created: 30/10/2017
Contributors: diana_burduja
Email: diana@burduja.eu
Tags: multistep checkout, multi step checkout, woocommerce, shop checkout, checkout steps
Requires at least: 3.0.1
Tested up to: 7.0
Stable tag: 2.35.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.2.4

Split the different sections of the default WooCommerce checkout page into multiple steps. Allow your customers a faster and easier checkout process.


== Description ==

Create a better user experience by splitting the checkout process in several steps. This will also improve your conversion rate.

The plugin was made with the use of the WooCommerce standard templates. This ensure that it should work with most the themes out there. Nevertheless, if you find that something isn't properly working, let us know in the Support forum.

= Features =

* Sleak design
* Mobile friendly
* Responsive layout
* Adjust the main color to your theme
* Inherit the form and buttons design from your theme
* Keyboard navigation

= Available translations = 

* German
* French

Tags: multistep checkout, multi-step-checkout, woocommerce, checkout, shop checkout, checkout steps, checkout wizard, checkout style, checkout page

== Installation ==

* From the WP admin panel, click "Plugins" -> "Add new".
* In the browser input box, type "Multi-Step Checkout for WooCommerce".
* Select the "Multi-Step Checkout for WooCommerce" plugin and click "Install".
* Activate the plugin.

OR...

* Download the plugin from this page.
* Save the .zip file to a location on your computer.
* Open the WP admin panel, and click "Plugins" -> "Add new".
* Click "upload".. then browse to the .zip file downloaded from this page.
* Click "Install".. and then "Activate plugin".

OR...

* Download the plugin from this page.
* Extract the .zip file to a location on your computer.
* Use either FTP or your hosts cPanel to gain access to your website file directories.
* Browse to the `wp-content/plugins` directory.
* Upload the extracted `wp-multi-step-checkout` folder to this directory location.
* Open the WP admin panel.. click the "Plugins" page.. and click "Activate" under the newly added "Multi-Step Checkout for WooCommerce" plugin.

== Frequently Asked Questions ==

= Why is the login form missing on the checkout page? =
Make sure to enable the `Display returning customer login reminder on the "Checkout" page` option on the `WP Admin -> WooCommerce -> Settings -> Accounts` page

= Is the plugin GDPR compatible? =
The plugin doesn't add any cookies and it doesn't modify/add/delete any of the form fields. It simply reorganizes the checkout form into steps.

= My checkout page still isn't multi-step, though the plugin is activated =
Make sure to purge the cache from any of the caching plugins, or of reverse proxy services (for example CloudFlare) you're using.

Another possible cause could be that the checkout page isn't using the default [woocommerce_checkout] shortcode. For example, the Elementor Pro checkout element replaces the default [woocommerce_checkout] shortcode with its HTML counterpart. Go to the "WP Admin -> Pages" page, open the checkout page for editing and make sure the [woocommerce_checkout] is present there.

== Screenshots ==

1. Login form
2. Billing
3. Review Order
4. Choose Payment
5. Settings page
6. On mobile devices

== Changelog ==

= 2.35.1 2026-01-01 =
* Declare compatibility WP7.0
* Declare WC template version for the form-checkout.php file

= 2.35 2026-05-08 =
* Fix: show the content of the Login step also before loading the JS
* Tweak: remove the Bootstrap JS library from the plugin's admin pages
* Tweak: move the output escaping at the point where the data is being outputted

= 2.34 2025-12-01 =
* Security: added escaping to input values for admin text inputs. Reported by benzdeus
* Fix: style steps to 100% width when there is only one step shown

= 2.33 2025-08-25 =
* Fix: _load_textdomain_just_in_time warning was showing up when activating the plugin

= 2.32 2025-03-20 =
* Feature: option for disabling the plugin on mobile devices
* Feature: option for toggling the default WooCommerce login form and the Login step

= 2.31 2024-11-22 =
* Fix: use the pro version, if both the free and the pro version are simultaneously active

= 2.30 2024-11-20 =
* Feature: add the "Hide the Shipping step if there are only virtual products in the cart" option
* Fix: remove the errors after changing the value of the input field

= 2.29 2024-09-10 =
* Tweak: adjust to the changes in the WooCommerce /templates/checkout/form-login.php file

= 2.28 2024-06-12 =
* Tweak: place the login validation error messages under the step tabs
* Show the "Inline validation errors" option

= 2.27 2024-02-11 =
* Compatibility with the Huntor theme
* Fix: the steps don't scroll up to the top on the Flatsome theme

[See changelog for all versions](https://plugins.svn.wordpress.org/wp-multi-step-checkout/trunk/changelog.txt).
