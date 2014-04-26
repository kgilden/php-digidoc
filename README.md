PHP DigiDoc
===========

[![Build Status](https://travis-ci.org/kgilden/php-digidoc.svg?branch=master)](https://travis-ci.org/kgilden/php-digidoc)

`PHP DigiDoc` is a PHP library for digitally signing and verifying digital
signatures using estonian id cards.

## Usage

```php
<?php

use KG\DigiDoc\Api;
use KG\DigiDoc\Client;

// This file is generated by Composer
require_once 'vendor/autoload.php';

$api = new Api(new Client());

// First off, let's create a new container.
$container = $api->create();

// Add the files you want to get signed.
$container->addFile('/path/to/file.txt');
$container->addFile('/second/path/to/file.md');

// Add a signature or two. Signature takes in certificate id and certificate
// signature. You must retrieve these from the client using the browser plugin.
$container->addSignature($signature = new Signature('F1..20', '8F..C0'));

// Sync up with the server. For example, the previous signature is given
// a challenge, which the client must solve.
$api->update($container);

printf("Challenge: %s\n", $signature->getChallenge());

// Set the solution for the given signature. This is computed by the borwser
// plugin.
$signature->setSolution('F6..00');

// Sync up with the server once more to send the solution.
$api->update($container);

// Time to write it on the disc.
$api->write('/tmp/my-newly-created-container.bdoc');

// Make sure to "close" the container (basically closes the session in the
// remote DigiDoc service).
$api->close($container);

```

## Installation

Install using [Composer](https://getcomposer.org/) as
[kgilden/php-digidoc](https://packagist.org/packages/kgilden/php-digidoc).

## Requirements

* PHP >= 5.3.8
* (php-soap, if you're on a Debian based distro)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Running the Tests

It's as simple as running `phpunit`.

## License

`PHP DigiDoc` is released under the MIT License.
See the bundled [LICENSE](LICENSE) file for details.

