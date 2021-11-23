# WooCommerce Reset

An experimental plugin providing REST API endpoints to reset the state of WooCommerce. It is being trialled in the Woo Admin E2E tests as am approach to isolating test.

☠️ 🛑 **WARNING! It is intended for use in test environments only. Do not use in production as it causes data loss**.

## Usage

Download plugin zip from GitHub and install in WordPress.

Or, with WP CLI:

```
wp plugin install https://github.com/woocommerce/woocommerce-reset/zipball/trunk/ --activate
```

## Development

```
composer install
npm install
```

## Tests

```
npm run test
```

