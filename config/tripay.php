<?php

return [
    'api_key'      => env('TRIPAY_APP_KEY'),
    'private_key'  => env('TRIPAY_PRIVATE_KEY'),
    'merchant_code'=> env('TRIPAY_MERCHANT_CODE'),
    'mode'         => env('TRIPAY_MODE', 'sandbox'),
    'base_url'     => env('TRIPAY_MODE') === 'production'
        ? 'https://tripay.co.id/api'
        : 'https://tripay.co.id/api-sandbox',
];
