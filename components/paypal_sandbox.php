<?php
// Include loadEnv function
include 'loadEnv.php';

// PayPal Sandbox credentials
$PAYPAL_EMAIL = $_ENV['PAYPAL_EMAIL'] ?? '';
$PAYPAL_PASSWORD = $_ENV['PAYPAL_PASSWORD'] ?? '';

if (empty($PAYPAL_EMAIL) || empty($PAYPAL_PASSWORD)) {
    throw new Exception('PayPal credentials are not set in .env file');
}

// Function to create PayPal payment
function createPayPalPayment($amount, $currency = 'USD')
{
    global $PAYPAL_EMAIL, $PAYPAL_PASSWORD;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, $PAYPAL_EMAIL . ':' . $PAYPAL_PASSWORD);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: en_US';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $token = json_decode($result)->access_token;

    // Create payment
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/payments/payment');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [
            [
                'amount' => [
                    'total' => $amount,
                    'currency' => $currency
                ]
            ]
        ],
        'redirect_urls' => [
            'return_url' => 'http://localhost/FinalProject_PHP/checkout.php?success=true',
            'cancel_url' => 'http://localhost/FinalProject_PHP/checkout.php?cancel=true'
        ]
    ]));

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return json_decode($result);
}

// Function to execute PayPal payment
function executePayPalPayment($paymentId, $payerId)
{
    global $PAYPAL_EMAIL, $PAYPAL_PASSWORD;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, $PAYPAL_EMAIL . ':' . $PAYPAL_PASSWORD);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: en_US';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $token = json_decode($result)->access_token;

    // Execute payment
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/payments/payment/' . $paymentId . '/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'payer_id' => $payerId
    ]));

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return json_decode($result);
}