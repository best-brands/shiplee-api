<?php

use BestBrands\Shiplee\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new Client('YOUR EMAIL', 'YOUR PASSWORD');
$client->authenticate();

$response = $client->get('zendingen/{YOUR SHIPMENT UUID}/label/pdf');

if (empty($response->getHeader('X-Guzzle-Redirect-History'))) {
    // Successfully created.
    file_put_contents('shipment.pdf', (string) $response->getBody());
} else {
    echo 'unable to find shipment';
}
