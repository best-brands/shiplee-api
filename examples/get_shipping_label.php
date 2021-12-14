<?php

use BestBrands\Shiplee\Client;

require dirname(__FILE__) . '/vendor/autoload.php';

$client = new Client('YOUR EMAIL', 'YOUR PASSWORD');
$client->authenticate();

$pdf = (string)$client->get('zendingen/{YOUR SHIPMENT UUID}/label/pdf')->getBody();

file_put_contents('shipment.pdf', $pdf);
