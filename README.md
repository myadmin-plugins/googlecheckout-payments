# MyAdmin Google Checkout Payments

Google Checkout payment processing plugin for the [MyAdmin](https://github.com/detain/myadmin) billing and hosting management platform. This package provides integration with the Google Checkout payment gateway, enabling order management, transaction viewing, and balance payment processing through the MyAdmin plugin system.

## Badges

[![Build Status](https://github.com/detain/myadmin-googlecheckout-payments/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-googlecheckout-payments/actions)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-googlecheckout-payments/version)](https://packagist.org/packages/detain/myadmin-googlecheckout-payments)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-googlecheckout-payments/downloads)](https://packagist.org/packages/detain/myadmin-googlecheckout-payments)
[![License](https://poser.pugx.org/detain/myadmin-googlecheckout-payments/license)](https://packagist.org/packages/detain/myadmin-googlecheckout-payments)

## Features

- Google Checkout payment gateway integration for MyAdmin
- Balance payment processing via Google Checkout
- Transaction and order viewing for administrators
- Sandbox and live environment support
- Event-driven architecture using Symfony EventDispatcher

## Installation

Install via Composer:

```sh
composer require detain/myadmin-googlecheckout-payments
```

## Configuration

The plugin registers the following settings through the MyAdmin settings system:

| Setting | Description |
|---------|-------------|
| `google_checkout_enabled` | Enable or disable Google Checkout |
| `google_checkout_sandbox` | Toggle between live and sandbox environments |
| `google_checkout_merchant_id` | Live Merchant ID |
| `google_checkout_merchant_key` | Live Merchant Key |
| `google_checkout_sandbox_merchant_id` | Sandbox Merchant ID |
| `google_checkout_sandbox_merchant_key` | Sandbox Merchant Key |

## Testing

Run the test suite with PHPUnit:

```sh
composer install
vendor/bin/phpunit
```

## License

Licensed under the LGPL-2.1. See the [LICENSE](LICENSE) file for details.
