=== WordPress Stripe Checkout Plugin ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: stripe, stripe payments, stripe checkout, credit card, payments
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell anything from your website with WordPress Stripe plugin. Accept Stripe payments in WordPress via streamlined checkout and mobile-ready payment form.

== Description ==

[Stripe WordPress](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin allows you to accept payments with the Stripe payment gateway. With a simple shortcode, you can quickly start accepting payments on a pre-built, Stripe-hosted form that is SCA-ready and supports 3D Secure 2 authentication. This makes accepting credit card payments easier than ever with very little setup and effort.

Stripe Checkout comes with a smart payment page that works seamlessly across devices and is designed to increase your conversion.

https://www.youtube.com/watch?v=x0JgyZ3l5mA&rel=0

=== Benefits of Stripe Checkout Payments ===

* Smooth checkout flow that automatically handles SCA (Strong Customer Authentication) requirements for you.
* Accept credit and debit card payments.
* Accept Apple Pay payments with no additional setup.
* Support Dynamic 3D Secure payment authentication.
* Localized for 14 languages.
* Automatically send email receipts to your customers.
* Build conversion-optimized payment forms, hosted on Stripe.
* Simplified mobile-ready experience for customers.
* It works on its own. There is no complex setup like a membership/e-commerce plugin.
* No setup fees, monthly fees or hidden costs. You are charged on a percentage basis for each payment (2.9% + 30 cents for International cards and 1.75% + 30 cents for domestic cards).
* Seamless transfer to your bank account. Once everything is set up, transfers arrive in your bank account on a 2-day rolling basis.
* Easily Switch between live and sandbox mode for testing.
* Real-time fee reporting in your Stripe account.
* Display a logo of your brand or product on the payment page.
* Send a purchase confirmation email to your customer after a transaction.
* Send a sale notification email to a chosen recipient (e.g. the seller) after a transaction.

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

You can use various template tags in the body of an email to dynamically change its content. You can find the full list of available template tags in the [Stripe payments](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

=== How to use Stripe Checkout ===

**Step 1: Enable Checkout in the Dashboard**

To begin using Checkout, log into the Stripe Dashboard and navigate to the Checkout settings (Settings > Stripe apps > CHECKOUT). From here you can enable the client integration and customize the look and feel of your checkout page. 

**Step 2: Create a Product**

Navigate to the "Products" section in the Dashboard and create a new product (New > One-time purchase products). When you create a product in the Dashboard, Stripe creates a SKU ID for it. You will need to use this SKU ID in shortcode to create a button.

**Step 3: Add a Webhook Endpoint**

Go to "Developers > Webhooks > Add endpoint" and insert the URL shown in the plugin settings. Select this event - "checkout.session.completed" and click "Add endpoint". This is where Stripe will send notification after a checkout payment is successful.

**Step 4: Add a Checkout Shortcode**

In order to create a Stripe checkout button you can add the following shortcode to a post/page:

`[wp_stripe_checkout_v3 sku="sku_EmRwzU81QKnkaq"]`

**sku** - SKU of the product created in your Stripe account.

=== Shortcode Parameters ===

You can add additional parameters in the shortcode to customize your Stripe checkout button.

* **button_text** - The text displayed inside the button (e.g. button_text="Pay Now"). The default is "Buy Now".
* **success_url** - The URL to which Stripe will redirect upon completion of a successful payment (e.g. success_url="https://example.com/success"). The default is the Return URL specified in the settings.
* **cancel_url** - The URL to which Stripe will redirect after a payment is canceled. (e.g. cancel_url="https://example.com/payment-canceled"). The default is the home URL for your site.

For detailed setup instructions please visit the [Stripe](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

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

= Does this plugin support strong customer authentication? =

Yes.

= Can this Stripe plugin be used to accept credit card payments on my website? =

Yes.

= Can this Stripe plugin be used to accept donations on my website? =

Yes.

= Can this Stripe plugin be used to accept Bitcoin payments on my website? =

Yes.

= Does this Stripe plugin support 1-tap payments on mobile phones and tablets? =

Yes.

= Does this Stripe plugin verify that credit cards are valid? =

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
