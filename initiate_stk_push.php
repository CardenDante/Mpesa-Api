<?php
// Added error log
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

file_put_contents('error_log.txt', "Script started\n", FILE_APPEND);

function formatPhoneNumberForMpesa($phoneNumber) {
    // Remove any non-numeric characters
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Check if the phone number starts with '254'
    if (substr($phoneNumber, 0, 3) !== '254') {
        // If the phone number starts with '0', replace it with '254'
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
    }

    // Ensure the phone number does not start with '+'
    $phoneNumber = ltrim($phoneNumber, '+');

    // Validate the number starts with '2547' or '2541' and has exactly 12 digits
    if (preg_match('/^254[17][0-9]{8}$/', $phoneNumber)) {
        return $phoneNumber;
    } else {
        // Return false or an error message if the format is incorrect
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('error_log.txt', "POST request received\n", FILE_APPEND);
    
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];

    $reference = abs(rand(1000000,99999999999));    
    $reference_one = "Lipagas Limited"; //replace with your needed, account reference.
    $reference_two = "Complete your order";
    // Update the callback URL to your live public URL
    $mobile_callback_url = 'http://172.206.71.75/home/qwerty/Mpesa-Api/mobile_callback_url.php';
    $timeout_callback_url = 'http://172.206.71.75/home/qwerty/Mpesa-Api/timeout_callback_url.php';

    // Live credentials
    $merchant_id = ''; // Replace with your live Business Shortcode
    $pass_key = ''; // Replace with your live Passkey
    $time_stamp = date("YmdHis", time());
    $consumer_key = ''; // Replace with your live Consumer Key
    $consumer_secret = ''; // Replace with your live Consumer Secret

    // Format the phone number
    $phone = formatPhoneNumberForMpesa($phone);
    if (!$phone) {
        file_put_contents('error_log.txt', "Invalid phone number format\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => "Invalid phone number format"]);
        exit;
    }

    $password = base64_encode($merchant_id . $pass_key . $time_stamp);

    // Authorization call
    $url_register = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url_register);
    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); 
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Change to true in production
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);

    if ($curl_response === false) {
        $error_message = curl_error($curl);
        file_put_contents('error_log.txt', "cURL Error: $error_message\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => "cURL Error: $error_message"]);
        exit;
    } else {
        file_put_contents('error_log.txt', "cURL Response: $curl_response\n", FILE_APPEND);
    }

    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_code !== 200) {
        echo json_encode(['success' => false, 'message' => "Unable to get access token. HTTP Code: $http_code"]);
        file_put_contents('error_log.txt', "HTTP Code: $http_code\n$curl_response\n", FILE_APPEND);
        exit;
    }

    $response_parts = explode('"access_token":', $curl_response);
    if (count($response_parts) < 2) {
        echo json_encode(['success' => false, 'message' => "Invalid response format. Response: $curl_response"]);
        file_put_contents('error_log.txt', "Invalid response format: $curl_response\n", FILE_APPEND);
        exit;
    }

    $token_part = explode('"', $response_parts[1]);
    $token = trim($token_part[1]);

    // M-Pesa STK push call
    $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; 

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token)); //setting custom header

    $curl_post_data = array(
        'BusinessShortCode' => $merchant_id,
        'Password' => $password,
        'Timestamp' => $time_stamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $merchant_id,
        'PhoneNumber' => $phone,
        'CallBackURL' => $mobile_callback_url,
        'AccountReference' => $reference_one . "-" . $reference,
        'TransactionDesc' => $reference_two,
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_code !== 200) {
        echo json_encode(['success' => false, 'message' => "Unable to initiate STK push. HTTP Code: $http_code"]);
        file_put_contents('error_log.txt', "HTTP Code: $http_code\n$curl_response\n", FILE_APPEND);
        exit;
    }

    $response_data = json_decode($curl_response, true);
    if (isset($response_data['ResponseCode']) && $response_data['ResponseCode'] == '0') {
        echo json_encode(['success' => true, 'message' => "STK push initiated successfully."]);
        file_put_contents('error_log.txt', "STK push initiated successfully. Response: $curl_response\n", FILE_APPEND);
    } else {
        echo json_encode(['success' => false, 'message' => "STK push failed. Response: $curl_response"]);
        file_put_contents('error_log.txt', "STK push failed. Response: $curl_response\n", FILE_APPEND);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Invalid request method."]);
    file_put_contents('error_log.txt', "Invalid request method\n", FILE_APPEND);
}
?>
