<?php

use BestBrands\Shiplee\Client;
use GuzzleHttp\RequestOptions;

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new Client('YOUR EMAIL', 'YOUR PASSWORD');
$client->authenticate();

$response = $client->post('zendingen/nieuw', [
    RequestOptions::FORM_PARAMS => [
        'product'                 => '', /* required:     @see \BestBrands\Shiplee\Enum */
        'sender_name'             => '', /* required:     the sender name */
        'sender_reference'        => '', /* not required: the sender reference (order id) */
        'recipient_full_name'     => '', /* required:     the recipient full name */
        'recipient_company_name'  => '', /* not required: the recipient full company name */
        'recipient_zipcode'       => '', /* required:     the recipient zipcode (formatting with or without space doesn't matter) */
        'recipient_number'        => '', /* required:     the recipient number */
        'recipient_suffix'        => '', /* not required: the recipient house number suffix */
        'recipient_street'        => '', /* required:     the recipient street */
        'recipient_place'         => '', /* required:     the recipient city */
        'recipient_phone'         => '', /* not required: the recipient phone */
        'recipient_email'         => '', /* not required: the recipient email */
        'option_allow_neighbours' => '', /* required:     allow neighboard delivery (y/n) */
        'max_attempts'            => '', /* required:     (hidden parameter, default is 2) */
        'note'                    => '', /* not required: delivery notes */
        'dropoff_point'           => '', /* required:     drop-off point uuid can be retrieved through $client->get('api/v1/dropoff-points'); */
        'delivery_date'           => '', /* required:     the delivery date, format d-m-Y */
        'submit'                  => 'Verzenden',
    ],
    'shiplee_requires_csrf'     => true, /* required: indicates CSRF token is required in the form parameters */
]);

$parcel_id = null;
$location = $response->getHeader('X-Guzzle-Redirect-History');

if (!empty($location)) {
    [$location] = $location;

    if (strpos($location, '/zendingen/') !== false) {
        $parts = explode('/', $location);
        $parcel_id = $parts[array_key_last($parts)];
    }
}

echo 'We should have a parcel ID on success: ' . $parcel_id . PHP_EOL;
