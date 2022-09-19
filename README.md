AMP Magento 1.x SOAP Client
=============================


⚠️ This repo is unmaintained ⚠️

[![Build Status](https://travis-ci.org/webgriffe/amp-magento-soap-client.svg?branch=master)](https://travis-ci.org/webgriffe/amp-magento-soap-client)

This is an asyncronous Magento 1.x SOAP client powered by [Amp](https://amphp.org/) and [clue/soap-react](https://github.com/clue/php-soap-react) client.

Installation
------------

Use [Composer](https://getcomposer.org/) require:

	composer require webgriffe/amp-magento-soap-client
	
Usage / Example
---------------

```php
<?php

require_once 'vendor/autoload.php';

\Amp\Loop::run(function () {
    /** @var \Webgriffe\AmpMagentoSoapClient\Client $client */
    $client = yield (new \Webgriffe\AmpMagentoSoapClient\Factory(
        'http://magento.host/api/soap/?wsdl',
        'username',
        'password',
        '8.8.8.8' // Optional nameserver IP
    ))->create();

    yield $client->login();
    $result = yield $client->call('catalog_product.list', []);
    var_dump($result);
});

```

License
-------

This library is under the MIT license. See the complete license in the LICENSE file.

Credits
-------
Developed by [Webgriffe®](http://www.webgriffe.com/).
