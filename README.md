[![Pipeline](https://gitlab.elias-haeussler.de/eliashaeussler/cpanel-requests/badges/master/pipeline.svg)](https://gitlab.elias-haeussler.de/eliashaeussler/cpanel-requests/-/pipelines)
[![Coverage](https://gitlab.elias-haeussler.de/eliashaeussler/cpanel-requests/badges/master/coverage.svg)](https://gitlab.elias-haeussler.de/eliashaeussler/cpanel-requests/)


# cPanel Requests

A simple PHP project to make API requests on your [cPanel](https://cpanel.com/) installation. This allows you to
call modules inside the installation and interact with them to add, show or list data such as domains, e-mail accounts,
databases and so on.

**The project makes use of [UAPI](https://documentation.cpanel.net/display/DD/Guide+to+UAPI). Therefore, it is required
to have a cPanel installation with at least version 42 running**.


## Installation

Install the project using Composer:

```bash
composer require eliashaeussler/cpanel-requests
```


## Usage

Connect to your cPanel instance first:

```php
$cPanel = new \EliasHaeussler\CpanelRequests\Application\CPanel('example.com', 'admin', 'password');
$cPanel->authorize();
```

Now you're able to make API requests:

```php
$response = $cPanel->api('<module>', '<function>', ['optional' => 'parameters']);
if ($response->isValid()) {
    // Do anything...
    // Returned data can be fetched using $response->getData()
}
```

**Note that currently only GET requests are supported.**

Visit the [official documentation](https://documentation.cpanel.net/display/DD/Guide+to+UAPI)
to get an overview about available API modules and functions.

### Example API request

```php
$cPanel = new \EliasHaeussler\CpanelRequests\Application\CPanel('cpanel.example.com', 'user', 'password');
$authorized = $cPanel->authorize();

if (!$authorized) {
    throw new \RuntimeException('Could not authorize at cPanel application.');
}

// Fetch domains from cPanel API
$response = $cPanel->api('DomainInfo', 'list_domains');

if (!$response->isValid()) {
    throw new \RuntimeException('Got invalid response from cPanel application.');
}

$domains = $response->getData()->data;
echo 'My main domain is: ' . $domains->main_domain;
```


## Command-line usage

The project provides a console which can be used to execute several functions from the command line.

```bash
# General usage
vendor/bin/cpanel-requests

# Clear expired request cookie files
vendor/bin/cpanel-requests clear:cookie
vendor/bin/cpanel-requests clear:cookie --lifetime 604800

# Clear log files
vendor/bin/cpanel-requests clear:logfile
```


## License

[GPL 3.0 or later](LICENSE)
