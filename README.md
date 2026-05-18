# Laradocker-API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thethunderturner/laradocker-api.svg?style=flat-square)](https://packagist.org/packages/thethunderturner/laradocker-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/thethunderturner/laradocker-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/thethunderturner/laradocker-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/thethunderturner/laradocker-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/thethunderturner/laradocker-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/thethunderturner/laradocker-api.svg?style=flat-square)](https://packagist.org/packages/thethunderturner/laradocker-api)

A powerful docker API, built for Laravel

>[!IMPORTANT]
> This plugin is still in early development, so please be patient until everything is implemented. Pull requests are welcome.

## Documentation
https://docs.docker.com/reference/api/engine/version/v1.54/

## Installation

You can install the package via composer:

```bash
composer require thethunderturner/laradocker-api
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laradocker-api-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laradocker-api-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laradocker-api-views"
```

## Setup

If you're getting errors like: **Failed to connect to localhost**, then you need to add docker to the group
```bash
sudo usermod -aG docker $USER
```
and reboot (or log out and in again) for changes to take effect. Then if you run
```
groups
```
you should see docker.

## Usage

```php
$docker = new TheThunderTurner\Docker();
echo $docker->echoPhrase('Hello, TheThunderTurner!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Matthew Biskas](https://github.com/thethunderturner)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
