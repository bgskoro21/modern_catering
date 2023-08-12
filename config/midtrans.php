<?php

return [
    /*
     * Set your Merchant Server Key
     */
    'serverKey' => env('MIDTRANS_SERVER_KEY'),

    /*
     * Set your Merchant Client Key
     */
    'clientKey' => env('MIDTRANS_CLIENT_KEY'),

    /*
     * Set the API URL
     */
    'apiUrl' => env('MIDTRANS_API_URL', 'https://api.midtrans.com/v2'),

    /*
     * Set the Payment Gateway environment
     */
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
     * Set the request data sanitization mode
     */
    'isSanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /*
     * Set the payment channels that are enabled
     */
    'enabled_payments' => [
        'bca_bank_transfer',
    ],

    /*
     * Set the payment channels that are required
     */
    'required_payments' => [
        'bca_bank_transfer',
    ],
];
