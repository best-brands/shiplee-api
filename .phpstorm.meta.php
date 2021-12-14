<?php

namespace PHPSTORM_META {
    registerArgumentsSet(
        'shiplee_endpoints_get',
        'zendingen/{shipment_uuid}/label/pdf',
        'zendingen/{shipment_uuid}/label/zpl',
        'zendingen/{shipment_uuid}/label/png',
        'zendingen/{shipment_uuid}/annuleren'
    );

    registerArgumentsSet(
        'shiplee_endpoint_post',
        'zendingen/nieuw',
    );

    expectedArguments(\BestBrands\Shiplee\Client::get(), 0, argumentsSet('shiplee_endpoints_get'));
    expectedArguments(\BestBrands\Shiplee\Client::post(), 0, argumentsSet('shiplee_endpoint_post'));
}
