<div align="center">

# cPanel Requests

[![Coverage](https://codecov.io/gh/eliashaeussler/cpanel-requests/branch/main/graph/badge.svg?token=YZ3RQHSX4B)](https://codecov.io/gh/eliashaeussler/cpanel-requests)
[![Maintainability](https://api.codeclimate.com/v1/badges/1277cb80151c332d04ff/maintainability)](https://codeclimate.com/github/eliashaeussler/cpanel-requests/maintainability)
[![Tests](https://github.com/eliashaeussler/cpanel-requests/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/cpanel-requests/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/cpanel-requests/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/cpanel-requests/actions/workflows/cgl.yaml)
[![Latest Stable Version](http://poser.pugx.org/eliashaeussler/cpanel-requests/v)](https://packagist.org/packages/eliashaeussler/cpanel-requests)
[![Total Downloads](http://poser.pugx.org/eliashaeussler/cpanel-requests/downloads)](https://packagist.org/packages/eliashaeussler/cpanel-requests)
[![License](http://poser.pugx.org/eliashaeussler/cpanel-requests/license)](LICENSE)

:package:&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/cpanel-requests) |
:floppy_disk:&nbsp;[Repository](https://github.com/eliashaeussler/cpanel-requests) |
:bug:&nbsp;[Issue tracker](https://github.com/eliashaeussler/cpanel-requests/issues)

</div>

A simple PHP project to make API requests on your [cPanel](https://cpanel.com/) installation.
This allows you to call modules inside the installation and interact with them to add, show or
list data such as domains, e-mail accounts, databases and so on.

**The project makes use of [UAPI](https://documentation.cpanel.net/display/DD/Guide+to+UAPI).
Therefore, it is required to have a cPanel installation with at least version 42 running**.

## :fire: Installation

```bash
composer require eliashaeussler/cpanel-requests
```

:warning: If you want to use two-factor authentication together with
the HTTP session [authorization](#authorization) method, you must
**manually require the `spomky-labs/otphp` package**.

## :zap:Usage

### Authorization

The following authorization methods are currently available:

| Type                                               | Implementation class                                                                                   |
|----------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| Authorization via [**API token**][1] (recommended) | [`Application\Authorization\TokenAuthorization`](src/Application/Authorization/TokenAuthorization.php) |
| Authorization via [**HTTP session**][2]            | [`Application\Authorization\HttpAuthorization`](src/Application/Authorization/HttpAuthorization.php)   |

:bulb: You can also provide your own implementation for authorization
at your cPanel instance. For this, you have to implement the interface
[`Application\Authorization\AuthorizationInterface`](src/Application/Authorization/AuthorizationInterface.php).

### Create a new [`CPanel`](src/Application/CPanel.php) instance

Once you have selected an authentication method, you can create a
new [`Application\CPanel`](src/Application/CPanel.php) instance:

```php
use EliasHaeussler\CpanelRequests\Application;

/** @var Application\Authorization\AuthorizationInterface $authorization */
$cPanel = new Application\CPanel($authorization, 'example.com', 2083);
```

### Perform API requests

Now you're able to make API requests:

```php
use EliasHaeussler\CpanelRequests\Application;

/** @var Application\CPanel $cPanel */
$response = $cPanel->api('<module>', '<function>', ['optional' => 'parameters']);
if ($response->isValid()) {
    // Do anything...
    // Response data can be fetched using $response->getData()
}
```

**Note that currently only GET requests are supported.**

Visit the [official documentation][3] to get an overview about
available API modules and functions.

## :bee: Example

```php
use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Http;

$authorization = new Application\Authorization\TokenAuthorization(
    username: 'bob',
    token: '9CKU401OH5WVDGSAVXN3UMLT8BJ5IY',
);
$cPanel = new Application\CPanel(
    authorization: $authorization,
    host: 'cpanel.bobs.site',
    port: 2083,
    protocol: Http\Protocol::Https,
);

// Fetch domains from cPanel API
$response = $cPanel->api(
    module: 'DomainInfo',
    function: 'list_domains',
);

if (!$response->isValid()) {
    throw new \RuntimeException('Got invalid response from cPanel application.');
}

$domains = $response->getData()->data;
echo 'Bob\'s main domain is: ' . $domains->main_domain;
```

## :wastebasket: Cleanup

The project provides a console application that can be used to execute
several cleanup commands from the command line.

```bash
# General usage
vendor/bin/cpanel-requests

# Remove expired request cookie files (default lifetime: 1 hour)
vendor/bin/cpanel-requests cleanup:cookies
vendor/bin/cpanel-requests cleanup:cookies --lifetime 1800

# Remove log files
vendor/bin/cpanel-requests cleanup:logs
```

## :technologist: Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## :star: License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).

[1]: https://api.docs.cpanel.net/cpanel/tokens/
[2]: https://api.docs.cpanel.net/cpanel/introduction/
[3]: https://documentation.cpanel.net/display/DD/Guide+to+UAPI
