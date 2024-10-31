=== Plugin Name ===
Contributors: weicai ling
Donate link: http://shop.bwxnet.com/
Tags: PayEase,RMB,CNY,Chinese Currency,orders,e-commerce
Requires at least: 2.7.1
Tested up to: 2.9
Stable tag: 1.5

Aother WordPress e-commerce plugin. A "Buy Now" button will be displayed on the post if "Price" is added in a custom field of the post.


== Description ==

Admin, authors can sell products/services by publishing a post with a custom field named "Price".
Parameters for delivery method and PayEase ( PayEase is an Internet Payment Gateway for for Chinese currency - CNY ) can be configured on "Payment Setting Page" under admin role.
Admin role can access all order records on "Orders Page".
Authors role can access their own orders on "Orders Page".


== Installation ==

1. Upload directory of `payease-payment` to the `/wp-content/plugins/bwx_payment` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure parameters on "Payment Setting Page" under admin role.
4. For post as product for sale, input "Price" or i18n of "Price" in the name of custom field, and the numeric price in the value of the custom field, publishing the post.

== Frequently Asked Questions ==

= What is PayEase  =

PayEase is a payment gateway supporting Chinese currency (CNY or RMB). The company of PayEase is based in Beijing China.
The website is : http://www.payeasenet.com/.

= Does the plugin support other payment gateways =
No.
Will add Paypal in next release.

= Can I run the plugin without the PayEase  =
Yes.
It will be an order record system without configuration of PayEase. Buyer's information will be recorded in the database and displayed on "order page". Buyer will pay you on site or remittance.

= What is format of delivery options in "Payment Setting Page" =
Delivery options seperated with ';'.  each option should have a name of delivery and a numeric cost for the delivery method seperated with ':'.


== Screenshots ==

1. Payment Setting Page - admin
2. Order Page - admin
3. Order Page - author
4. Post with "Buy Now" button.
5. User information interface.

== Changelog ==

= 1.5 =
The first release.

== Upgrade Notice ==




