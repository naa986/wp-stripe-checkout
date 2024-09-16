=== WP Stripe Checkout ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: stripe, ecommerce, apple pay, google pay, credit card
Requires at least: 5.3
Tested up to: 6.6
Stable tag: 1.2.2.48
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept Stripe payments in WordPress with Stripe Checkout plugin. Sell anything in WordPress with Stripe one-time and recurring payments.

== Description ==

[Stripe Checkout](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin for WordPress allows you to accept payments with the Stripe payment gateway. With a simple shortcode, you can quickly start accepting payments on a pre-built, Stripe-hosted form that is SCA-ready and supports 3D Secure 2 authentication. This makes accepting credit card payments easier than ever with very little setup and effort.

https://www.youtube.com/watch?v=ynHVLiiARyQ&rel=0

=== WP Stripe Checkout Add-ons ===

* [Variable Price](https://noorsplugin.com/how-to-add-a-price-field-to-a-stripe-payment-button/)
* [Variable Quantity](https://noorsplugin.com/how-to-add-a-quantity-field-to-a-stripe-payment-button/)
* [Submit Type](https://noorsplugin.com/how-to-customize-the-type-of-the-stripe-payment-page-button/)
* [Terms of Service](https://noorsplugin.com/how-to-show-a-terms-of-service-checkbox-on-the-stripe-payment-page/)
* [Payment Link Email](https://noorsplugin.com/how-to-prefill-email-addresses-for-stripe-payment-link-buttons/)
* [Variable Currency](https://noorsplugin.com/wp-stripe-checkout-variable-currency/)
* [WP User Tracking](https://noorsplugin.com/wp-stripe-checkout-wordpress-user-tracking/)
* [WP User Only Button](https://noorsplugin.com/wp-stripe-checkout-wordpress-user-only-button/)

=== Benefits of Stripe Payments ===

* Easy Stripe payment integration.
* Stripe payment links integration.
* Accept credit and debit card payments.
* Accept recurring subscription payments via payment links.
* Accept donation payments.
* Accept Apple Pay payments.
* Accept Google Pay payments.
* Accept Alipay payments.
* Accept WeChat Pay payments.
* Accept Bancontact payments.
* Accept EPS payments.
* Accept giropay payments.
* Accept iDEAL payments.
* Accept Przelewy24 payments.
* Accept Sofort payments.
* Accept Afterpay/Clearpay payments.
* Accept Boleto payments.
* Accept OXXO payments.
* Accept ACH Direct Debit payments.
* Accept Bacs Direct Debit payments.
* Accept BECS Debit payments.
* Accept Canadian pre-authorised debit (PAD) payments.
* Accept SEPA Direct Debit payments.
* Support Dynamic 3D Secure payment authentication.
* Support payment processing with Stripe test cards.
* Support phone number collection at checkout.
* Support user redeemable promotion codes at checkout.
* Localized for different languages.
* Automatically email Stripe receipts to your customers.
* Simplified mobile-ready experience for customers.
* No complex setup like a membership/e-commerce plugin.
* Easily Switch between live and sandbox mode for testing.
* Send a purchase confirmation email to your customer after a transaction.
* Send a sale notification email to a chosen recipient (e.g. the seller) after a transaction.
* Automatic VAT/tax ID collection at checkout

=== WordPress Stripe Plugin Configuration ===

Once you have activated the plugin, you need to configure some settings related to your Stripe merchant account. It's located under "WP Stripe Checkout -> Settings -> General".

* Test Mode: A checkbox that allows you to run Stripe transactions on your site in test mode using test API keys.
* Stripe Test Secret Key: Your Stripe secret key to run transactions in test mode
* Stripe Test Publishable Key: Your Stripe publishable key to run transactions in test mode
* Stripe Live Secret Key: Your Stripe secret key to run transactions in live mode
* Stripe Live Publishable Key: Your Stripe publishable key to run transactions in live mode
* Currency Code: The default currency code that will be used when accepting a payment
* Return URL: The page URL to which the customer will be redirected after a successful payment
* Stripe Webhook URL: The page URL to which Stripe will send notification after an event

=== Emails ===

Stripe checkout plugin comes with an "Emails" tab where you will be able to configure some email related settings.

**Email Sender Options**

In this section you can choose to customize the default From Name and From Email Address that will be used when sending an email.

**Purchase Receipt Email**

When this feature is enabled an email sent to the customer after completion of a successful purchase. Options you can customize here:

* The subject of the purchase receipt email
* The content type of the purchase receipt email. The default is "text/plain". But you can also set it to "text/html"
* The body of the purchase receipt email.

**Sale Notification Email**

When this feature is enabled an email is sent to your chosen recipient after completion of a successful purchase. Options you can customize here:

* The subject of the sale notification email
* The content type of the sale notification email. The default is "text/plain". But you can also set it to "text/html"
* The body of the sale notification email.

You can use various template tags in the body of an email to dynamically change its content. You can find the full list of available template tags in the [Stripe](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

=== Webhook Endpoint ===

Go to "Developers > Webhooks > Add endpoint" and insert the URL shown in the plugin settings. Select this event - "checkout.session.completed" and click "Add endpoint". This is where Stripe will send a notification after a checkout payment is successful.

You will also need to add the "checkout.session.async_payment_succeeded" and "checkout.session.async_payment_failed" events if you plan to use a payment method where there can be a delay in payment confirmation. For example:

* Bacs Direct Debit
* Boleto
* Canadian pre-authorised debits
* OXXO
* SEPA Direct Debit
* SOFORT
* ACH Direct Debit

For detailed setup instructions please visit the [Stripe WordPress](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

=== How to use Stripe Checkout ===

The easiest way to start accepting Stripe payments is to add the following shortcode to a post/page:

`[wp_stripe_checkout_session name="My Product" price="2.99"]`

Replace the value of "name" with your product name and "price" with the actual product price.

= Shortcode Parameters =

You can add additional parameters in the shortcode to customize your Stripe payment button.

* **description** - The description of the product (e.g. description="My product description"). This is optional and no description is set by default.
* **button_text** - The text displayed inside the button (e.g. button_text="Pay Now"). The default is "Buy Now".
* **button_image** - The image that will act as the button (e.g. button_image="https://example.com/wp-content/uploads/pay-now-button.png"). The default is a plain button with the text "Buy Now".
* **success_url** - The URL to which Stripe will redirect upon completion of a successful payment (e.g. success_url="https://example.com/success"). The default is the Return URL specified in the settings.
* **cancel_url** - The URL to which Stripe will redirect after a payment is canceled. (e.g. cancel_url="https://example.com/payment-canceled"). The default is the home URL for your site.
* **billing_address** - Specify whether Checkout should collect the customer's billing address. (e.g. billing_address="required"). The default is "" (Checkout will only attempt to collect the billing address when necessary).
* **phone_number_collection** - Specify whether Checkout should collect the customer's phone number. (e.g. phone_number_collection="true").
* **allow_promotion_codes** - Specify whether Stripe should allow user redeemable promotion codes at checkout. (e.g. allow_promotion_codes="true").
* **tax_id_collection** - Specify whether Checkout should automatically show the tax ID collection form depending on your customer's location. (e.g. tax_id_collection="true").
* **consent_collection_promotions** - Specify whether Checkout should automatically collect consent from customers so you can send them promotional emails. (e.g. consent_collection_promotions="auto").
* **prefill_wp_email** - Specify whether the plugin should automatically pass the user's email address to Stripe. (e.g. prefill_wp_email="true"). This requires the user to be logged in to WordPress.
* **class** - Custom CSS classes for the button (e.g. class="btn"). Multiple CSS classes can be added in a space-delimited format (e.g. class="btn btn2 btn3").
* **target** - Specify whether the button should open in a new tab. (e.g. target="_blank").

=== How to use Stripe Payment Links ===

https://www.youtube.com/watch?v=M0lMMlJVw4M&rel=0

This method allows you to integrate Stripe payment links with the plugin.

**Step 1: Create a Payment Link**

Log in to your Stripe account dashboard and navigate to the "Payment links" page (Payments > Payment links). Select an existing product or add a new one to create a payment link.

**Step 2: Use the Payment Link in a Shortcode**

In order to create a button with the payment link you can add the following shortcode to a post/page:

`[wp_stripe_checkout_payment_link url="https://buy.stripe.com/live_6gPE4jw7dMbUKdd3345"]`

**url** - URL of the payment link created in your Stripe account.

= Shortcode Parameters =

You can add additional parameters in the shortcode to customize your Stripe payment link button.

* **button_text** - The text displayed inside the button (e.g. button_text="Pay Now"). The default is "Buy Now".
* **button_image** - The image that will act as the button (e.g. button_image="https://example.com/wp-content/uploads/pay-now-button.png"). The default is a plain button with the text "Buy Now".

For detailed setup instructions please visit the [Stripe payments](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

== Installation ==

1. Go to the Add New plugins screen in your WordPress Dashboard
1. Click the upload tab
1. Browse for the plugin file (wp-stripe-checkout.zip) on your computer
1. Click "Install Now" and then hit the activate button

== Frequently Asked Questions ==

= Can I accept Stripe payments with this plugin? =

Yes.

= Can I accept Stripe WooCommerce payments with this plugin? =

No. This is not a WooCommerce plugin.

= Can I use this Stripe plugin to accept credit or debit card payments in WordPress? =

Yes.

= Can I use this plugin to accept Stripe recurring subscription payments in WordPress? =

Yes.

= Can I use this plugin to accept donations in WordPress? =

Yes.

= Can I use a Stripe credit card for simulating purchases? =

Yes. 

== Screenshots ==

1. Stripe Payments plugin Demo
2. Stripe Plugin Orders Menu
3. Stripe Plugin Email Sender Options
4. Stripe Plugin Purchase Receipt Email Settings
5. Stripe Plugin Sale Notification Email Settings

== Upgrade Notice ==
none

== Changelog ==

= 1.2.2.48 =
* Added a filter before a sessions is created.

= 1.2.2.47 =
* Added support for the WordPress User Only Button add-on.

= 1.2.2.46 =
* Added an option to manually add WordPress user ID to an order.

= 1.2.2.45 =
* Added an option to verify webhook notification.

= 1.2.2.44 =
* Added support for the target parameter in the deprecated wp_stripe_checkout shortcode.

= 1.2.2.43 =
* Added shortcode parameter to open button in a new tab.

= 1.2.2.42 =
* Improved shortcode sanitization suggested by Patchstack.

= 1.2.2.41 =
* The session ID is passed to the success page.

= 1.2.2.40 =
* Made changes to the code that retrieve the plugin url and path.

= 1.2.2.39 =
* Added support for variable currency.

= 1.2.2.38 =
* Better debug logging.

= 1.2.2.37 =
* Additional check for the settings link.

= 1.2.2.36 =
* Added a deprecated notice to the product menu.

= 1.2.2.35 =
* Added an interface to edit order metadata.

= 1.2.2.34 =
* Added product name meta to the order.

= 1.2.2.33 =
* Added an optional parameter to set a description of the product.

= 1.2.2.32 =
* Added an option to automatically pass the user's email address to Stripe.

= 1.2.2.31 =
* Added an option to collect consent from customers to send promotional emails.

= 1.2.2.30 =
* Added support for minimum and maximum quantity for variable quantity.

= 1.2.2.29 =
* Added an option to collect VAT/tax ID at checkout.

= 1.2.2.28 =
* Added an option to load Stripe scripts on every page.

= 1.2.2.27 =
* Added parameters to manually add payment method types.

= 1.2.2.26 =
* Added support for Stripe payment link email add-on.

= 1.2.2.25 =
* Added support for Stripe payment links.

= 1.2.2.24 =
* Added support for email tags in the subject.

= 1.2.2.23 =
* Rolled back to the previous Stripe API.

= 1.2.2.22 =
* The plugin is now compatible with Stripe API version 2022-11-15.

= 1.2.2.21 =
* Made some security related improvements suggested by wpscan.

= 1.2.2.20 =
* Added support for the Terms of Service add-on. It can be used to show a terms of service checkbox on the Stripe payment page.

= 1.2.2.19 =
* Fixed a price formatting issue with the JPY currency.

= 1.2.2.18 =
* Fixed a bug with quantity in the wp_stripe_checkout_v3 shortcode.

= 1.2.2.17 =
* Added an option to disable nonce check on the front end.

= 1.2.2.16 =
* Fixed an issue where the correct styling was not getting applied to template 1 on a standalone page.

= 1.2.2.15 =
* Fixed a minor bug where the client reference id was not getting set.

= 1.2.2.14 =
* Added support for the Submit Type add-on. It can be used to customize the submit button that appears on the Stripe payment page.

= 1.2.2.13 =
* Added an option to allow user redeemable promotion codes at checkout.

= 1.2.2.12 =
* Added email tags for the billing and shipping addresses.

= 1.2.2.11 =
* Added support for various payment methods.

= 1.2.2.10 =
* WordPress 6.0 compatibility update.

= 1.2.2.9 =
* Added a new product display template.

= 1.2.2.8 =
* Fixed a bug that was causing the checkout button to not function correctly.

= 1.2.2.7 =
* Added variable quantity support to wp_stripe_checkout_v3 shortcode.

= 1.2.2.6 =
* Added a new interface for creating products.

= 1.2.2.5 =
* Added support for variable quantity.

= 1.2.2.4 =
* Added support for phone number collection at checkout.

= 1.2.2.3 =
* Made some changes to the settings area.

= 1.2.2.2 =
* Added support for billing address collection in the checkout session shortcode.
* Added support for the Variable Price add-on that allows buyers to donate or pay their desired amount for a product.

= 1.2.2.1 =
* Added a new shortcode that can be used to accept Stripe payments with the checkout session API.

= 1.2.2 =
* Added Product ID and Price ID to order data.

= 1.2.1 =
* Added the button_image parameter that can be included in the shortcode to use an image as the button.
* Added the class parameter that can be included in the shortcode to apply custom CSS classes to a button. 

= 1.2.0 =
* Added the shipping_address parameter that can be used to make the shipping address collection required.

= 1.1.9 =
* Added the billing_address parameter that can be used to make the billing address collection required.

= 1.1.8 =
* Added the locale parameter that can be used to localize the display of Checkout.

= 1.1.7 =
* Added support for recurring payments
* Errors are now visible when a payment button is clicked

= 1.1.6 =
* Fixed a bug in the cancel_url parameter

= 1.1.5 =
* Made some security related improvements in the plugin

= 1.1.4 =
* Replaced sku with the price parameter in the shortcode.

= 1.1.3 =
* Made some improvements to the orders menu.

= 1.1.2 =
* Fixed a warning notice in the orders menu.

= 1.1.1 =
* Added a new check to make sure that a return URL page is configured in the settings.

= 1.1.0 =
* Fixed an issue where the product name was not getting captured with the order.

= 1.0.9 =
* Added a new checkout method that supports strong customer authentication.

= 1.0.8 =
* The email address field is now prefilled for a logged-in WordPress user.

= 1.0.7 =
* Added a new email tag {customer_email} that can be used to show the email address of the customer.

= 1.0.6 =
* Stripe checkout shortcode now accepts a success_url parameter that can be used to override the default Return URL specified in the settings.

= 1.0.5 =
* Fixed this intermittent issue: if there are several buttons on the same page the charge would apply with the highest amount regardless of which button was clicked.

= 1.0.4 =
* A new customer is now created for each purchase. With this feature, a seller will be able to charge the customer later from their Stripe account.

= 1.0.3 =
* Fixed a bug that caused this error - "Cannot load wp-stripe-checkout-settings".
* The plugin can now send a purchase receipt email to the customer. It can also send a sale notification email to a chosen recipient.

= 1.0.2 =
* Updated some permalinks in the plugin

= 1.0.1 =
* First commit
