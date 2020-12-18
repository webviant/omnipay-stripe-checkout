# omnipay-stripe-checkout

**Stripe (Checkout) driver for the Omnipay PHP payment processing library**

Omnipay implementation of the Stripe payment gateway using their "Checkout" process.

[![Build Status](https://travis-ci.org/webviant/omnipay-stripe-checkout.png?branch=master)](https://travis-ci.org/webviant/omnipay-stripe-checkout)
[![Coverage Status](https://coveralls.io/repos/github/digitickets/omnipay-stripe-checkout/badge.svg?branch=master)](https://coveralls.io/github/digitickets/omnipay-stripe-checkout?branch=master)
[![Latest Stable Version](https://poser.pugx.org/digitickets/omnipay-stripe-checkout/version.png)](https://packagist.org/packages/digitickets/omnipay-stripe-checkout)
[![Total Downloads](https://poser.pugx.org/digitickets/omnipay-stripe-checkout/d/total.png)](https://packagist.org/packages/digitickets/omnipay-stripe-checkout)

## Installation

**Important: Driver requires [PHP's Intl extension](http://php.net/manual/en/book.intl.php) and [PHP's SOAP extension](http://php.net/manual/en/book.soap.php) to be installed.**

The Stripe (Checkout) Omnipay driver is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "webviant/omnipay-stripe-checkout": "~1.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## What's Included

This driver handles transactions being processed by the ["Checkout" process of Stripe](https://stripe.com/docs/payments/checkout).

It is [Strong Customer Authentication](https://stripe.com/docs/strong-customer-authentication)\-aware.

It assumes you will use the [redirect method in the browser page](https://stripe.com/docs/payments/checkout/one-time#redirect-checkout) (ie that you will not host the card form yourself).

We have done the minimum necessary to get our system to work. If you need any further functionality, please submit a pull request (or ask us to make the change)

It supports refunds, but currently it only handles refunding the full amount of a transaction.

**Note:** the Stripe API does _not_ support negative- or zero-value items, so this driver filters them out. **Be aware** that the amount the customer pays
is only calculated from that filtered set of items, so if there are deductions, etc in your cart, they will _not_ be taken off the payment amount.
And of course zero-value items will not be shown on the card form.

## What's Not Included

TBC

## Basic Usage

For general Omnipay usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you believe you have found a bug in this driver, please report it using the [GitHub issue tracker](https://github.com/digitickets/omnipay-stripe-checkout/issues),
or better yet, fork the library and submit a pull request.
