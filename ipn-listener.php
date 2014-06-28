<?php
error_reporting(E_STRICT);
require('ipn-message.php');

header('HTTP/1.1 200 OK');

$tester = array(
    'mc_gross'                => '19.95',
    'protection_eligibility'  => 'Eligible',
    'address_status'          => 'confirmed',
    'payer_id'                => 'LPLWNMTBWMFAY',
    'tax'                     => '0.00',
    'address_street'          => '1 Main St',
    'payment_date'            => '20:12:59 Jan 13, 2009 PST',
    'payment_status'          => 'Completed',
    'charset'                 => 'windows-1252',
    'address_zip'             => '95131',
    'first_name'              => 'Test',
    'mc_fee'                  => '0.88',
    'address_country_code'    => 'US',
    'address_name'            => 'Test User',
    'notify_version'          => '2.6',
    'custom'                  => '',
    'payer_status'            => '',
    'verifiedaddress_country' => 'United States',
    'address_city'            => 'San Jose',
    'quantity'                => '1',
    'verify_sign'             => 'AtkOfCXbDm2hu0ZELryHFjY-Vb7PAUvS6nMXgysbElEn9v-1XcmSoGtf',
    'payer_email'             => 'gpmac_1231902590_per@paypal.com',
    'txn_id'                  => '61E67681CH3238416',
    'payment_type'            => 'instant',
    'last_name'               => 'User',
    'address_state'           => 'CA',
    'receiver_email'          => 'gpmac_1231902686_biz@paypal.com',
    'payment_fee'             => '0.88',
    'receiver_id'             => 'S8XGHLYDW9T3S',
    'txn_type'                => 'express_checkout',
    'item_name'               => '',
    'mc_currency'             => 'USD',
    'item_number'             => '',
    'residence_country'       => 'US',
    'test_ipn'                => '1',
    'handling_amount'         => '0.00',
    'transaction_subject'     => '',
    'payment_gross'           => '19.95',
    'shipping'                => '0.00'
);

//Process IPN
$message = new BreakfastCraft\IPNMessage($_POST);

if ($message->isIPNValid()) {
    $message->writeMessage();
} else {
    $error = array(
        'error'      => 'Bad stuff has happend!',
        'ipn'        => $message->ipn,
        'sketchyIPN' => $message->sketchyIPN,
        'response'   => $message->response
    );
    file_put_contents('messages.json', json_encode($error));
}
