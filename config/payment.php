<?php 
/**
 * @author Kunal Baniya
 * SurePay EGov Payment Gateway
 * Payment details for Development/UAT Server
 */
return [
    'txn_amount' => env('TXN_AMOUNT', '225.00'),
    'checksum_secret_key' => env('CHECKSUM_SECRET_KEY', 'd486233e3f533b21478e49d3770318c1866cd4e54fa5f440d40ffb08a5d8ae7a'),
    'merchant_id' => env('MERCHANT_ID', 'UATNFDCLSG0000001628'),
    'service_id' => env('SERVICE_ID', 'NFDCFFOTEST01'),
    'message_type' => env('MESSAGE_TYPE', '0100'),
    'process_request_url' => env('PROCESS_REQUEST_URL', 'https://pilot.surepay.ndml.in/SurePayPayment/sp/processRequest'),
    'success_url' => env('SUCCESS_URL', 'https://web.uneecopscloud.com/ffo_uat/app/pg-redirect-page?q=success'),
    'failed_url' => env('FAILED_URL', 'https://web.uneecopscloud.com/ffo_uat/app/pg-redirect-page?q=fail'),
    'bulk_payment_status_api_url' => 'https://pilot.surepay.ndml.in/SurePayPayment/v1/bulkPaymentStatusAPI',
    'basic_auth_username' => 'UATNFDCLSG0000001628',
    'basic_auth_password' => 'qadeputzxhhowigoywis',
    'currency_code' => env('CURRENCY_CODE', 'INR')
];


/**
 * @author Kunal Baniya
 * SurePay EGov Payment Gateway
 * Payment details for Production Server
 */
//  return [
//     'txn_amount' => env('TXN_AMOUNT', '225.00'),
//     'checksum_secret_key' => env('CHECKSUM_SECRET_KEY', 'adae3466c29952cf082ff050248baecd7d711dfff110d1a4312da28a9ddd9669'),
//     'merchant_id' => env('MERCHANT_ID', 'NFDCFFOCG0000000369'),
//     'service_id' => env('SERVICE_ID', 'NFDCFFOTEST01'),
//     'message_type' => env('MESSAGE_TYPE', '0100'),
//     'process_request_url' => env('PROCESS_REQUEST_URL', 'https://surepay.ndml.in/SurePayPayment/sp/processRequest'),
//     'success_url' => env('SUCCESS_URL', 'https://ffo.gov.in/app/pg-redirect-page?q=success'),
//     'failed_url' => env('FAILED_URL', 'https://ffo.gov.in/app/pg-redirect-page?q=fail'),
//     'bulk_payment_status_api_url' => 'https://surepay.ndml.in/SurePayPayment/v1/bulkPaymentStatusAPI',
//     'basic_auth_username' => 'NFDCFFOCG0000000369',
//     'basic_auth_password' => 'dxecyysuxxbpqrhifhbr',
//     'currency_code' => env('CURRENCY_CODE', 'INR')
// ];

