=== Split Order's Payment ===
Contributors: omarmonterrey
Donate link: https://omarmonterrey.com/
Tags: woocommerce, ecommerce, payment, split
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows your customers to split their payment between many of them.

== Description ==

This plugin allows your customers to split order payments between many of them. The payments will be threated as sub orders under the main order and there are many options to evenly divide the payment or allow each payeer to input their amount.

## How does it works for customers?
1. User selects **Split payment** as payment method instead of any of your payment methods.
2. When the order is placed, the customer will receive it's own invitation and be able to invite other payees.
3. The invitations are paid with your other payment methods. Invitations can't be paid using **Split Payment**
4. When all the invitations are paid (Equal Parts) or the order is completly paid (Allow Custom Amount), it's marked as completed and processed

## How does it works for site administrators?
* Only the main order will be visible on the Wordpress Backend, **Split Payment** payment's orders will be shown in the order's listing page and under the edit order section if the order has **Split Payment** as payment method.
* All subpayments are processed by other payment methods, no setup required
* Email settings are available under "Woocommerce > Settings > Email" tab, there you can change subject and read how you can alter the template for invitations.

== Screenshots ==

1. The settings of the plugin in the Payments area of woocommerce settings

2. Email received by users when they are invited to make their part of a payment

3. Notice that customers see when they select this payment method

4. What customers see on their checkout page where they can invite payeers


== Changelog ==

= 1.0.0 =
* First version of this plugin uploaded for review

== Frequently Asked Questions ==

= How will the invitations be paid? =

When an invitation link is clicked, it will show information to the user about that order, the invitation and the amount (Or allow the user to input a custom amount). When "Pay invitation" button is clicked, the customer will be redirected to checkout page with a "Split Payment" item with the price of the invitation to be paid. User will then pay using any other payment method besides Split Payment and it will be added under the initial order.

= With this plugin can my customers invite a third party to pay their order? =

Yes, all they need to do is send the invitation to the person who will pay and cancel their own invitation, then the invited payeer will be able to pay all of the order.

== Upgrade Notice ==

= 1.0.0 =
This is the first version