=== Pomelo Payment Gateway for WooCommerce ===
Contributors: pomelopay
Tags: pomelopay, pomelo, payments, woocommerce, payment gateway, credit card, apple pay, google pay, bancontact, ideal, giropay, grabpay, wechatpay, alipay
Requires at least: 4.8
Tested up to: 5.7
Stable tag: 1.0.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Secure and easy payments in WooCommerce using Pomelo Payment Gateway. Integrate easily with 25+ payment methods.

== Description ==

No hassle payments

> Accept Payments Anywhere and easily by integrating with over 25+ methods in one single gateway

Pomelo Pay is on a mission to make accepting online payments easier. By integrating both credit, debit and local card payments you can start selling online easily. Offer your customers more than 25+ payment methods in multiple currencies.

= CARD PAYMENT METHODS =

Credit & Debit cards:

* MasterCard (Debit & Credit)
* Visa (Debit, Credit & Prepaid)
* American Express (Debit & Credit)
* Discover (Debit & Credit)
* UnionPay (Debit & Credit)
* JCB (Debit & Credit)

= MOBILE WALLETS =

* Apple Pay
* Google Pay
* Alipay
* WeChatPay
* GrabPay
* Unionpay QR

= LOCAL PAYMENT METHODS =

* WeChatPay
* Alipay
* iDeal
* Bancontact
* Giropay
* Przelewy24
* SOFORT
* Klarna
* Zimpler
* Trustly

= CRYPTOCURRENCIES =

* Bitpay


Create a merchant account on our [merchant dashboard](https://www.pomelopay.com) and start taking payments today.

> No contract, quick sign-up and no hidden fees. Next-day payouts. Quicker, Safer & Smarter payments

= FEATURES =

* Integrate easily with 25+ payment methods
* Next day pay-outs
* No-code setup, install the plugin and enter your API keys
* Easy sandbox mode for testing before launching our store
* Configure payment expiry and sync your payment links
* Production testing using test payment gateway
* Get dedicated developer support
* Debug events using the details secure logger
* Offer multi-currency payments to your customers

== Frequently Asked Questions ==

= Where do I get my API keys? =

Create a merchant account on our [merchant dashboard](https://www.pomelopay.com)
Once you have an account you can find your production API keys under the "Connect" menu option

= How can I test payments without actually paying? =

You can immediately get setup on production but enable a “test payment provider”. If you’re already close to going live and just want to test the final flows you will be using this. These transactions will go through your production account but won’t actually be paid out. It always you to smoke test your integration with a fake payment provider but using your live credentials. This also means you can see all payments in the dashboard, app etc..

We have a full sandbox which allows you more of a development approach. This allows you to test with multiple payment providers and these transactions won’t show up in your account. You will be given a sandbox API endpoint, client ID and API key from us and you can use these to initiate transactions and integrate in your account.
For sandbox credentials please contact us at developers@pomelopay.com

= What is the payment expiry setting? =

You can decide how long you want an initiated payment to be payable by the customer. Once it has expired and has not been completed the order will be automatically cancelled.
If you do not set this value it will take the default configured value from your dashboard settings on Pomelo Pay

= Any other question? =

Please contact us on our  [website](https://www.pomelopay.com) or email us at developers@pomelopay.com

= How can I contribute to the source code or translations? =

Check out our development repository on [Github](http://github.com/PomeloPay/pomelo-woocommerce)

== Screenshots ==

1. The settings screen for your plugin, easily select sandbox or production and add your specific keys.
2. Payment method selection for easy checkout.

== Installation ==

= Minimum Requirements =

* PHP version >= 7.0
* cURL, JSON, bcrypt
* WordPress >= 4.8
* WooCommerce >= 3.5.5

= Wordpress Plugin installation =

1. Search for Pomelo Pay on Plugins > New Plugin.
2. Activate the plugin in your Wordpress admin > Plugins
3. Enter your sandbox or production client_id and client_secret from Pomelo Pay
4. Enable the plugin in either sandbox or production mode, you're now good to go

= Developer manual installation =

0. Make sure you have SFTP/SSH or Git access to your Wordpress installation directory on your server.
1. Download the plugin package
2. Upload the unzipped folder 'pomelo-woocommerce' to the `/wp-content/plugins/` directory
3. Enable the Pomelo Payment Gateway plugin through the 'Plugins' menu in WordPress
4. Enter your sandbox or production client_id and client_secret from Pomelo Pay
5. Enable the plugin in either sandbox or production mode, you're now good to go

= Updating =

We will ensure backwards compatibility for minor version update. For major version upgrades (e.g. 1.0.0 to 2.0.0) make sure you have a backup and use our sandbox to test

== Changelog ==

= 1.0.3 - 15-05-2021 =

* Fixed a default setting for sandbox mode

= 1.0.2 - 15-05-2021 =

* NEW Feature, select your destination page after an order is received
* Improved event, redirect handling
* Improved settings description

= 1.0.1 - 15-03-2021 =

* Fixed a bug that would cause the sandbox to be used even when left unchecked

= 1.0.0 - 15-03-2021 =

* NEW Initial release
