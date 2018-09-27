<p align="">
    <a href="http://www.xe.com" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/en/5/55/XE_Corporation_logo.png" width="90" height="72"/>
    </a>
</p>

# XE Currency Data Client - PHP

XE.com Inc. is the World's Trusted Currency Authority. This project provides an SDK to interface with our XE Currency Data (XECD) product.

XE Currency Data is a REST API that gives you access to daily or live rates and historic mid-market conversion rates between all of our supported currencies. 

You will need an api key and secret to use this sdk. Sign up for a [free trial][5] or register for a [full account][6].

## Installation

The preferred way to install this package is through [composer][4].

```
composer require xe/xecd-rates-client
```

This package follows [semantic versioning][3].

## Usage

```php
<?php
require 'vendor/autoload.php';

use Xe\Xecd\Client\Rates\XecdRatesClientAspectKernel;
use Xe\Xecd\Client\Rates\Exception\XecdRatesException;
use GuzzleHttp\Exception\RequestException;

XecdRatesClientAspectKernel::getInstance()->init([
    'cacheDir' => '/your/cache/directory',
]);

$xecdRatesClient = XecdRatesClient::create(<YOUR_ACCOUNT_ID>, <YOUR_API_KEY>);

try
{
    $conversions = $xecdRatesClient->convertFrom(new Currency('CAD'), Currencies::wildcard(), 12345.67)->getBody();
    foreach ($conversions->getConversions() as $currency => $currencyConversions) {
        foreach ($currencyConversions as $timestamp => $conversion) {
            echo "{$conversion->getFromAmount()} {$conversion->getFromCurrency()} = {$conversion->getToAmount()} {$conversion->getToCurrency()}\n";
        }
    }
} catch (XecdRatesException $e) {
    // API errors with error code.
} catch (Exception $e) {
    // ALl other errors, such as connection timeout errors.
}
```

## Documentation

[Technical Specifications][2]

## Contributing

xecd-rates-client-php is an open-source project. Submit a pull request to contribute!

## Testing

```bash
cd xecd-rates-client-php
composer install

# Unit tests.
phpunit --testsuite Unit

# Integration tests.
export XECD_RATES_API_ACCOUNT_ID=<YOUR_API_ACCOUNT_ID>
export XECD_RATES_API_KEY=<YOUR_API_KEY>
phpunit --testsuite Integration
```

## Security Issues

If you discover a security vulnerability within this package, please **DO NOT** publish it publicly. Instead, contact us at **security [at] xe.com**. We will follow up with you as soon as possible.

## About Us

[XE.com Inc.][1] is The World's Trusted Currency Authority. Development of this project is led by the XE.com Inc. Development Team and supported by the open-source community.

[1]: http://www.xe.com
[2]: http://www.xe.com/xecurrencydata/XE_Currency_Data_API_Specifications.pdf
[3]: http://semver.org/
[4]: http://getcomposer.org/download/
[5]: https://xecd.xe.com/account/signup.php?freetrial
[6]: http://www.xe.com/xecurrencydata/
