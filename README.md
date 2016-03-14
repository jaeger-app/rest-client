# Backup Pro REST Client

A simple REST cient to interact with Backup Pro installations.

## Installation
Add `backup-pro/rest-client` as a requirement to `composer.json`:

```bash
$ composer require backup-pro/rest-client
```

## Simple Example


```php
use \mithra62\BpApiClient\Client;

$client = new Client();
$backups = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->get('/backups');

```
## Authentication

Backup Pro uses HMAC-SHA authentication which is a simple key / secret paradigm to create hashed signatures. You can get/set your api key and secret, as well as the API URL endpoint, from your individual Backup Pro installations. 

## Error Handling

If anything goes wrong with a request the library will return an [ApiProblem](http://tools.ietf.org/html/draft-nottingham-http-problem-07 "ApiProblem") object. Here's an example:

```php
use \mithra62\BpApiClient\Client;
use \mithra62\BpApiClient\ApiProblem;

$client = new Client();
$backups = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->get('/backups');

if($result instanceof ApiProblem) 
{
    if($result->getStatus() == 403) {
        //authentication issue
    }

	$result->getTitle() //API problem response title
	$result->getDetail() //API problem response details
	
}

```

## Hal Responses

For all successful responses from the Backup Pro API, the library will return an instance of `\mithra62\BpApiClient\Hal` object which is a wrapper for [\Nocarrier\Hal](https://github.com/blongden/hal). 

```php
use \mithra62\BpApiClient\Client;
use \mithra62\BpApiClient\Hal;

$client = new Client();
$backups = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->get('/backups');

if($result instanceof Hal) 
{
    $data = $result->getData();
    $resources = $result->getResources();
}

```

## Examples

Since Backup Pro follows the [Richardson Maturity Model](Richardson Maturity Model) there are helper methods available for each HTTP verb. Below are some simple usecase examples and their implementations

### Take a Backup

```php
use \mithra62\BpApiClient\Client;

$client = new Client();
$backups = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->post('/backups');

```

### Update Settings

```php
use \mithra62\BpApiClient\Client;

$client = new Client();
$settings = array('working_directory' => '/path/to/working_directory');
$update = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->put('/settings', $settings);

```

### Get Settings

```php
use \mithra62\BpApiClient\Client;

$client = new Client();
$settings = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->get('/settings');

```

### Get Storage Locations

```php
use \mithra62\BpApiClient\Client;

$client = new Client();
$storage_locations = $client->setApiKey($api_key)
                 ->setApiSecret($api_secret)
                 ->setSiteUrl($api_endpoint_url)
                 ->get('/storage');

```