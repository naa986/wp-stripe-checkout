=== WP Stripe Checkout ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: stripe, payment, checkout, e-commerce, credit card, apple pay, google pay, store, sales, sell, shop, cart, payments
Requires at least: 5.3
Tested up to: 6.0
Stable tag: 1.2.2.16
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept Stripe payments in WordPress with WordPress Stripe Checkout plugin. Sell anything in WordPress with Stripe one-time and recurring payments.

== Description ==

[Stripe Checkout](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin for WordPress allows you to accept payments with the Stripe payment gateway. With a simple shortcode, you can quickly start accepting payments on a pre-built, Stripe-hosted form that is SCA-ready and supports 3D Secure 2 authentication. This makes accepting credit card payments easier than ever with very little setup and effort.

Stripe Checkout comes with a smart payment page that works seamlessly across devices and is designed to increase your conversion.

=== Benefits of Stripe Checkout Payments ===

* Connect Stripe to WordPress and use Stripe for payments.
* Smooth checkout flow that automatically handles SCA (Strong Customer Authentication) requirements for you.
* Easy Stripe payment integration.
* Accept credit and debit card payments.
* Accept recurring subscription payments.
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
* Localized for 34 languages.
* Automatically email Stripe receipts to your customers.
* Build conversion-optimized payment forms, hosted on Stripe.
* Simplified mobile-ready experience for customers.
* It works on its own. There is no complex setup like a membership/e-commerce plugin.
* No setup fees, monthly fees or hidden costs. You are charged on a percentage basis for each payment (2.9% + 30 cents per successful card charge).
* Seamless transfer to your bank account. Once everything is set up, transfers arrive in your bank account on a 2-day rolling basis.
* Easily Switch between live and sandbox mode for testing.
* Real-time fee reporting in your Stripe account.
* Display a logo of your brand or product on the Stripe payment page.
* Easy payouts for Stripe merchants.
* Enable Stripe invoicing after the payment.
* Send a purchase confirmation email to your customer after a transaction.
* Send a sale notification email to a chosen recipient (e.g. the seller) after a transaction.

=== WP Stripe Checkout Add-ons ===

* [Variable Price](https://noorsplugin.com/how-to-add-a-price-field-to-a-stripe-payment-button/)
* [Variable Quantity](https://noorsplugin.com/how-to-add-a-quantity-field-to-a-stripe-payment-button/)
* [Submit Type](https://noorsplugin.com/how-to-customize-the-type-of-the-stripe-payment-page-button/)

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

=== How to use Stripe Checkout (Option 1) ===

The easiest way to start accepting Stripe payments is to create a product in the plugin interface (Stripe Checkout > All Products > Add New).

Once you have created a product, add the shortcode for it to a post/page.

`[wp_stripe_checkout id="1"]`

Replace the value of "id" with your product ID.

= Product Display Template =

The template parameter in the shortcode allows you to use a pre-designed display template to showcase your product.

`[wp_stripe_checkout id="1" template="1"]`

Replace the value of "template" with your preferred display template id.

=== How to use Stripe Checkout (Option 2) ===

If you do not wish to create a product you can get started by adding the following shortcode to a post/page:

`[wp_stripe_checkout_session name="My Product" price="2.99"]`

Replace the value of "name" with your product name and "price" with the actual product price.

= Shortcode Parameters =

You can add additional parameters in the shortcode to customize your Stripe payment button.

* **button_text** - The text displayed inside the button (e.g. button_text="Pay Now"). The default is "Buy Now".
* **button_image** - The image that will act as the button (e.g. button_image="https://example.com/wp-content/uploads/pay-now-button.png"). The default is a plain button with the text "Buy Now".
* **success_url** - The URL to which Stripe will redirect upon completion of a successful payment (e.g. success_url="https://example.com/success"). The default is the Return URL specified in the settings.
* **cancel_url** - The URL to which Stripe will redirect after a payment is canceled. (e.g. cancel_url="https://example.com/payment-canceled"). The default is the home URL for your site.
* **billing_address** - Specify whether Checkout should collect the customer's billing address. (e.g. billing_address="required"). The default is "" (Checkout will only attempt to collect the billing address when necessary).
* **phone_number_collection** - Specify whether Checkout should collect the customer's phone number. (e.g. phone_number_collection="true").
* **class** - Custom CSS classes for the button (e.g. class="btn"). Multiple CSS classes can be added in a space-delimited format (e.g. class="btn btn2 btn3").

=== How to use Stripe Checkout (Option 3) ===

https://www.youtube.com/watch?v=x0JgyZ3l5mA&rel=0

**Step 1: Enable Checkout in the Dashboard**

To begin using Checkout, log into the Stripe Dashboard and navigate to the Checkout settings (Settings > Stripe apps > CHECKOUT). From here you can enable the client integration and customize the look and feel of your checkout page. 

**Step 2: Create a Product**

Navigate to the "Products" section in the Dashboard and create a new product (New > One-time purchase products). When you create a product in the Dashboard, Stripe creates a Price ID for it. You will need to use this Price ID in shortcode to create a button.

**Step 3: Add a Webhook Endpoint**

Go to "Developers > Webhooks > Add endpoint" and insert the URL shown in the plugin settings. Select this event - "checkout.session.completed" and click "Add endpoint". This is where Stripe will send notification after a checkout payment is successful.

**Step 4: Add a Checkout Shortcode**

In order to create a Stripe checkout button you can add the following shortcode to a post/page:

`[wp_stripe_checkout_v3 price="price_UY9NozbEy7T3PUlk"]`

**price** - Price ID of the product created in your Stripe account.

To create a checkout button for accepting recurring subscription payments, you need to set the "mode" parameter to "subscription" in the shortcode:

`[wp_stripe_checkout_v3 price="price_UY9NozbEy7T3PUlk" mode="subscription"]`

The product in question must also be of type "Recurring" in your Stripe account.

= Shortcode Parameters =

You can add additional parameters in the shortcode to customize your Stripe checkout button.

* **button_text** - The text displayed inside the button (e.g. button_text="Pay Now"). The default is "Buy Now".
* **button_image** - The image that will act as the button (e.g. button_image="https://example.com/wp-content/uploads/pay-now-button.png"). The default is a plain button with the text "Buy Now".
* **success_url** - The URL to which Stripe will redirect upon completion of a successful payment (e.g. success_url="https://example.com/success"). The default is the Return URL specified in the settings.
* **cancel_url** - The URL to which Stripe will redirect after a payment is canceled. (e.g. cancel_url="https://example.com/payment-canceled"). The default is the home URL for your site.
* **mode** - The mode of the checkout (e.g. mode="subscription"). The default is "payment".
* **locale** - The locale that will be used to localize the display of Checkout (e.g. locale="en"). The default is "auto" (Stripe detects the locale of the browser).
* **billing_address** - Specify whether Checkout should collect the customer's billing address. (e.g. billing_address="required"). The default is "" (Checkout will only attempt to collect the billing address when necessary).
* **shipping_address** - Specify whether Checkout should collect the customer's shipping address. (e.g. shipping_address="required").
* **shipping_countries** - If you only wish to ship to certain countries you can specify their country codes. (e.g. shipping_countries="'US'" or shipping_countries="'US','GB','AU'").
* **class** - Custom CSS classes for the button (e.g. class="btn"). Multiple CSS classes can be added in a space-delimited format (e.g. class="btn btn2 btn3").

For detailed setup instructions please visit the [Stripe payments](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

=== Legacy Stripe Checkout ===

https://www.youtube.com/watch?v=0C_gqAMCSpo&rel=0

Stripe Checkout Form is a beautiful payment form specifically designed for desktop, tablet, and mobile devices. Your customer never go to an external payment page for making the payments. They stay on your site and enter their credit card in a secure payment form to complete the payment.

All payment submissions are made via a secure HTTPS connection. However, in order to fully protect sensitive customer data, you must serve the page containing the Stripe payment form over HTTPS. In short, the address of the page containing the Stripe checkout form must start with "https://" rather than just "http://".

In order to create a Stripe payment button you can add the following shortcode to a post/page:

`[wp_stripe_checkout item_name="Champion Men's Jersey T-Shirt" description="Short-sleeve t-shirt in athletic fit featuring ribbed crew neckline and logo at chest" amount="59.99" label="Pay Now"]`

In order to accept donations for a cause you can use the shortcode like the following:

`[wp_stripe_checkout item_name="Watsi's medical work" description="Donations for Watsi's medical work" amount="1.00" label="Donate to Watsi"]`

You can add additional parameters in the shortcode to customize your stripe payment button.

* **item_name** - The name of the item you are selling.
* **name** - The name of your company or website.
* **image** - A URL pointing to a image of your brand or product(128x128px recommended). The recommended image types are .gif, .jpg, and .png.
* **locale**- Specify auto to display Checkout in the customer's preferred language, if available (English is used by default).
* **currency** - The currency of the item (e.g. currency="USD"). If not specified it will take the default currency code from the settings.
* **billing-address** - Specify whether Checkout form should collect the customer's billing address (e.g. billing-address="true"). The default is false.
* **shipping-address** - Specify whether Checkout form should collect the customer's shipping address (e.g. shipping-address="true"). The default is false.
* **panel-label** - The label of the payment button in the Checkout form (e.g. panel-label="Pay $2.00"). Checkout does not translate custom labels to the customer's preferred language.
* **zip-code** - Specify whether Checkout form should validate the customer's billing postal code (e.g. zip-code="true"). The default is false.
* **label** - The text that is displayed on the blue payment button (e.g. label="Buy Now"). Default is "Pay with Card". Checkout does not translate this label at the moment.
* **allow-remember-me** - Specify whether to exclude the option to "Remember Me" for future purchases (e.g. allow-remember-me="false"). The default is true.
* **bitcoin** - Specify whether Checkout form should accept Bitcoin (e.g. bitcoin="true"). The default is false.
* **success_url** - Specify whether Checkout form should redirect the customer to a different url upon completion of a successful payment (e.g. success_url="https://example.com/success"). The default is the Return URL specified in the settings.

For detailed setup instructions please visit the [Stripe](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

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

= What payment methods are supported? =

You can use payment methods that are supported by the Stripe payment gateway.

= Can I use this Stripe plugin to accept credit or debit card payments in WordPress? =

Yes.

= Can I use this plugin to accept Stripe recurring subscription payments in WordPress? =

Yes.

= Can I use this plugin to accept Stripe donations in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Google Pay payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Apple Pay payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Alipay payments in WordPress? =

Yes

= Can I use this Stripe plugin to accept WeChat Pay payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Bancontact payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept EPS payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept giropay payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept iDEAL payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Przelewy24 payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Sofort payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Afterpay/Clearpay payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Boleto payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept OXXO payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept ACH Direct Debit payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Bacs Direct Debit payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept BECS Debit payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept Canadian pre-authorised debit payments in WordPress? =

Yes.

= Can I use this Stripe plugin to accept SEPA Direct Debit payments in WordPress? =

Yes.

= Can I use a Stripe credit card for simulating purchases? =

Yes.

= My Stripe pay button is not working. What can I do? =

You can post your issue on the Stripe plugin page: https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/. 


== Screenshots ==

1. Stripe Payments plugin Demo
2. Stripe Plugin Orders Menu
3. Stripe Plugin Email Sender Options
4. Stripe Plugin Purchase Receipt Email Settings
5. Stripe Plugin Sale Notification Email Settings

== Upgrade Notice ==
none

== Changelog ==

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
