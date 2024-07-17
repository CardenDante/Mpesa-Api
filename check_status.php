<?php
// This is a simulation script. Replace this with actual logic to check status from the database or another data source.
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'check_status_log.txt');

$callbackLog = file_get_contents('mobile_callback_log.txt');

if (strpos($callbackLog, 'Payment successful') !== false) {
    echo json_encode(['success' => true, 'message' => 'Payment successful.']);
} elseif (strpos($callbackLog, 'Payment failed or cancelled') !== false) {
    echo json_encode(['success' => false, 'message' => 'Payment failed or cancelled.']);
} elseif (strpos($callbackLog, 'Transaction timed out or failed') !== false) {
    echo json_encode(['success' => false, 'message' => 'Transaction timed out or failed.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No status update yet.']);
}

