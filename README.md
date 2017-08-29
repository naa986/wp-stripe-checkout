# Stripe Checkout for WordPress

## Description

[WordPress Stripe](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) plugin allows you to accept payments with the Stripe payment gateway. You can integrate Stripe's payment form into your website with a simple shortcode. This makes accepting credit card payments easier than ever with very little setup and effort. It was developed by [noorsplugin](https://noorsplugin.com/) and is currently being used on over 200 websites.

## What is Stripe Checkout?

Stripe Checkout Form is a beautiful payment form specifically designed for desktop, tablet, and mobile devices. Your customer never go to an external payment page for making the payments. They stay on your site and enter their credit card in a secure payment form to complete the payment.

## Does Stripe Checkout require HTTPS?

All payment submissions are made via a secure HTTPS connection. However, in order to fully protect sensitive customer data, you must serve the page containing the Stripe payment form over HTTPS. In short, the address of the page containing the Stripe checkout form must start with "https://" rather than just "http://".

## Why Stripe Checkout?

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

## Supported Countries

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

## Stripe Plugin Setup

Once you have activated the plugin, you need to configure some settings related to your Stripe merchant account. It's located under **WP Stripe Checkout > Settings**.

* Stripe Test Secret Key
* Stripe Test Publishable Key
* Stripe Live Secret Key
* Stripe Live Publishable Key
* Currency Code
* Return URL

In order to create a Stripe payment button you can add the following shortcode to a post/page:
```
[wp_stripe_checkout item_name="Champion Men's Jersey T-Shirt" description="Short-sleeve t-shirt in athletic fit featuring ribbed crew neckline and logo at chest" amount="59.99" label="Pay Now"]
```
In order to accept donations for a cause you can use the shortcode like the following:
```
[wp_stripe_checkout item_name="Watsi's medical work" description="Donations for Watsi's medical work" amount="1.00" label="Donate to Watsi"]
```
## Stripe Shortcode Parameters

You can add additional parameters in the shortcode to customize your stripe payment button.

* **item_name** - The name of the item you are selling.
* **name** - The name of your company or website.
* **image** - A URL pointing to a image of your brand or product(128x128px recommended). The recommended image types are .gif, .jpg, and .png.
* **locale**- Specify auto to display Checkout in the customer's preferred language, if available (English is used by default).
* **currency** - The currency of the item (e.g. **currency="USD"**). If not specified it will take the default currency code from the settings.
* **billing-address** - Specify whether Checkout form should collect the customer's billing address (e.g. **billing-address="true"**). The default is false.
* **shipping-address** - Specify whether Checkout form should collect the customer's shipping address (e.g. **shipping-address="true"**). The default is false.
* **panel-label** - The label of the payment button in the Checkout form (e.g. **panel-label="Pay $2.00"**). Checkout does not translate custom labels to the customer's preferred language.
* **zip-code** - Specify whether Checkout form should validate the customer's billing postal code (e.g. **zip-code="true"**). The default is false.
* **label** - The text that is displayed on the blue payment button(e.g. **label="Buy Now"**). Default is **Pay with Card**. Checkout does not translate this label at the moment.
* **bitcoin** - Specify whether Checkout form should accept Bitcoin (e.g. **bitcoin="true"**). The default is false.
* **alipay** - Specify whether Checkout form should accept Alipay (e.g. **alipay="true"**). The default is false.

## Documentation

For detailed documentation please visit the [Stripe Plugin](https://noorsplugin.com/stripe-checkout-plugin-for-wordpress/) page.
