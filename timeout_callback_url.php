<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'timeout_callback_log.txt');

file_put_contents('timeout_callback_log.txt', "Timeout callback received\n", FILE_APPEND);

// Read the incoming callback data
$callbackJSONData = file_get_contents('php://input');
$callbackData = json_decode($callbackJSONData, true);

file_put_contents('timeout_callback_log.txt', "Callback Data: " . print_r($callbackData, true) . "\n", FILE_APPEND);

// Handle timeout specific logic here
file_put_contents('timeout_callback_log.txt', "Transaction timed out or failed\n", FILE_APPEND);

