<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'mobile_callback_log.txt');

file_put_contents('mobile_callback_log.txt', "Callback received\n", FILE_APPEND);

// Read the incoming callback data
$callbackJSONData = file_get_contents('php://input');
$callbackData = json_decode($callbackJSONData, true);

file_put_contents('mobile_callback_log.txt', "Callback Data: " . print_r($callbackData, true) . "\n", FILE_APPEND);

if (isset($callbackData['Body']['stkCallback'])) {
    $stkCallback = $callbackData['Body']['stkCallback'];
    $resultCode = $stkCallback['ResultCode'];
    $resultDesc = $stkCallback['ResultDesc'];

    // Check the status of the transaction
    if ($resultCode == 0) {
        // Payment was successful
        file_put_contents('mobile_callback_log.txt', "Payment successful: " . $resultDesc . "\n", FILE_APPEND);
    } else {
        // Payment failed or was cancelled
        file_put_contents('mobile_callback_log.txt', "Payment failed or cancelled: " . $resultDesc . "\n", FILE_APPEND);
    }
} else {
    file_put_contents('mobile_callback_log.txt', "Invalid callback data\n", FILE_APPEND);
}

