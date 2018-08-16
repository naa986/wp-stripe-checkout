=== Stripe Checkout ===
Contributors: naa986
Donate link: https://noorsplugin.com/
Tags: stripe, e-commerce, ecommerce, sell, sales, store, cart, checkout, shop, payments, selling
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell anything from your WordPress site with Stripe. Accept credit card payments via streamlined checkout and mobile-ready payment form.

== Description ==

[WordPress Stripe Checkout](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin allows you to accept payments with the Stripe payment gateway. You can integrate Stripe's payment form into your website with a simple shortcode. This makes accepting credit card payments easier than ever with very little setup and effort.

https://www.youtube.com/watch?v=0C_gqAMCSpo&rel=0

= What is Stripe Checkout Form? =

Stripe Checkout Form is a beautiful payment form specifically designed for desktop, tablet, and mobile devices. Your customer never go to an external payment page for making the payments. They stay on your site and enter their credit card in a secure payment form to complete the payment.

= Does Stripe Checkout require HTTPS? =

All payment submissions are made via a secure HTTPS connection. However, in order to fully protect sensitive customer data, you must serve the page containing the Stripe payment form over HTTPS. In short, the address of the page containing the Stripe checkout form must start with "https://" rather than just "http://".

= Why Stripe Checkout? =

* Take Credit card payments easily and directly on your store.
* Accept almost any type of credit or debit card such as Visa, MasterCard, American Express, JCB, Discover, Diners Club.
* Accept donations directly on your website
* Accept gift and prepaid cards.
* Support for other payment methods such as Bitcoin and China's Alipay.
* Simplified mobile-ready experience for customers.
* Optimized payment form designed to maximize customer conversion.
* Option to collect customer's billing address during checkout.
* Option to Collect customer's shipping address during checkout.
* It works on its own. There is no complex setup like a membership/e-commerce plugin.
* No setup fees, monthly fees or hidden costs. You are charged on a percentage basis for each payment (2.9% + 30 cents for International cards and 1.75% + 30 cents for domestic cards).
* Seamless transfer to your bank account. Once everything is set up, transfers arrive in your bank account on a 2-day rolling basis. 
* Easily Switch between live and sandbox mode for testing.
* Real-time fee reporting in your Stripe account.
* Display a logo of your brand or product on the checkout form.
* Option to verify the card's zipcode during checkout.
* Allow your customers to pay with a localized experience during checkout (12 languages supported and growing).

= Supported Countries =

Stripe is currently available for businesses in 25 countries:

* Australia
* Canada
* Denmark
* Finland
* France
* Ireland
* Japan
* Norway
* Singapore
* Spain
* Sweden
* United Kingdom
* United States
* Austria (BETA)
* Belgium (BETA)
* Germany (BETA)
* Hong Kong (BETA)
* Italy (BETA)
* Luxembourg (BETA)
* Netherlands (BETA)
* Portugal (BETA)
* Brazil (PRIVATE BETA)
* Mexico (PRIVATE BETA)
* New Zealand (PRIVATE BETA)
* Switzerland (PRIVATE BETA)

If you're running businesses from one of these countries, you'll be able to accept payments from customers anywhere in the world.

= Plugin Setup =

Once you have activated the plugin, you need to configure some settings related to your Stripe merchant account. It's located under "WP Stripe Checkout -> Settings".

* Stripe Test Secret Key
* Stripe Test Publishable Key
* Stripe Live Secret Key
* Stripe Live Publishable Key
* Currency Code
* Return URL

In order to create a Stripe payment button you can add the following shortcode to a post/page:

`[wp_stripe_checkout item_name="Champion Men's Jersey T-Shirt" description="Short-sleeve t-shirt in athletic fit featuring ribbed crew neckline and logo at chest" amount="59.99" label="Pay Now"]`

In order to accept donations for a cause you can use the shortcode like the following:

`[wp_stripe_checkout item_name="Watsi's medical work" description="Donations for Watsi's medical work" amount="1.00" label="Donate to Watsi"]`

= Shortcode Parameters =

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

For detailed setup instructions please visit the [Stripe Checkout](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin page.

== Installation ==

1. Go to the Add New plugins screen in your WordPress Dashboard
1. Click the upload tab
1. Browse for the plugin file (wp-stripe-checkout.zip) on your computer
1. Click "Install Now" and then hit the activate button

== Frequently Asked Questions ==

= Can this plugin be used to accept credit card payments on my website? =

Yes.

= Can this plugin be used to accept donations on my website? =

Yes.

= Can this plugin be used to accept Bitcoin payments on my website? =

Yes.

= Does this plugin support 1-tap payments on mobile phones and tablets? =

Yes.

= Does Stripe Checkout verify that credit cards are valid? =

Yes.

== Screenshots ==

1. Stripe Checkout Demo
2. Stripe Checkout Orders Menu
3. Stripe Checkout Email Sender Options
4. Stripe Checkout Purchase Receipt Email Settings
5. Stripe Checkout Sale Notification Email Settings

== Upgrade Notice ==
none

== Changelog ==

= 1.0.3 =
* Fixed a bug that caused this error - "Cannot load wp-stripe-checkout-settings".
* The plugin can now send a purchase receipt email to the customer. It can also send a sale notification email to a chosen recipient.

= 1.0.2 =
* Updated some permalinks in the plugin

= 1.0.1 =
* First commit
