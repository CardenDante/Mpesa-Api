<?php
// Added error log
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

file_put_contents('error_log.txt', "Script started\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('error_log.txt', "POST request received\n", FILE_APPEND);
    
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];

    $reference = abs(rand(1000000,99999999999));    
    $reference_one = "Chacha Testing";
    $reference_two = "Pay ksh.1 to get now a instant delivery";
    // Update the callback URL to your live public URL
    $mobile_callback_url = 'https://api.lipagas.co/mobile_callback_url.php';
    $timeout_callback_url = 'https://527c-102-0-6-10.ngrok-free.app/timeout_callback_url.php';

    // Live credentials
    $merchant_id = '174379'; // Replace with your live Business Shortcode
    $pass_key = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; // Replace with your live Passkey
    $time_stamp = date("YmdHis", time());
    $consumer_key = 'LDJ4WmvmQsHcKdaM2zzaHmT153hVvJ2AYAvbrFDcgmGqW1AW'; // Replace with your live Consumer Key
    $consumer_secret = 'lFXj1uL2ZzCifDweGPSz6aSm68seoJOPlilGi4QwCFO3QVAfEGcljcCkYjuxfKDb'; // Replace with your live Consumer Secret

    $phone = (int) filter_var($phone, FILTER_SANITIZE_NUMBER_INT);							
    $password = base64_encode($merchant_id . $pass_key . $time_stamp);

    // Authorization call
    $url_register = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

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
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; 

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
        'AccountReference' => $reference_one,
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
