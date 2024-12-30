<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ApiService
{
  protected $CI;
  protected $secret_key;
  protected $caller_base_url;
  protected $username;
  protected $password;

  public function __construct()
  {
    $this->CI = &get_instance();
    $this->CI->load->database();

    switch ($_ENV['APP_ENV']) {
      case 'development':
        // Key for encryption (must be the same on both ends)
        $this->secret_key = $_ENV['DEVELOPMENT_SECRET_KEY'];
        $this->caller_base_url = $_ENV['DEVELOPMENT_CALLER_BASE_URL'];

        // Credentials for API Platforms
        $this->username = $_ENV['DEVELOPMENT_USERNAME'];
        $this->password = $_ENV['DEVELOPMENT_PASSWORD'];
        break;

      case 'local':
        // Key for encryption (must be the same on both ends)
        $this->secret_key = $_ENV['LOCAL_SECRET_KEY'];
        $this->caller_base_url = $_ENV['LOCAL_CALLER_BASE_URL'];

        // Credentials for API Platforms
        $this->username = $_ENV['LOCAL_USERNAME'];
        $this->password = $_ENV['LOCAL_PASSWORD'];
        break;

      case 'live':
        $this->secret_key = $_ENV['LIVE_SECRET_KEY'];
        $this->caller_base_url = $_ENV['LIVE_CALLER_BASE_URL'];

        $this->username = $_ENV['LIVE_USERNAME'];
        $this->password = $_ENV['LIVE_PASSWORD'];
        break;

      case 'staging':
        $this->secret_key = $_ENV['STAGING_SECRET_KEY'];
        $this->caller_base_url = $_ENV['STAGING_CALLER_BASE_URL'];

        $this->username = $_ENV['STAGING_USERNAME'];
        $this->password = $_ENV['STAGING_PASSWORD'];
        break;

      case 'uat':
        $this->secret_key = $_ENV['UAT_SECRET_KEY'];
        $this->caller_base_url = $_ENV['UAT_CALLER_BASE_URL'];

        $this->username = $_ENV['UAT_USERNAME'];
        $this->password = $_ENV['UAT_PASSWORD'];
        break;

      case 'production':
        $this->secret_key = $_ENV['PRODUCTION_SECRET_KEY'];
        $this->caller_base_url = $_ENV['PRODUCTION_CALLER_BASE_URL'];

        $this->username = $_ENV['PRODUCTION_USERNAME'];
        $this->password = $_ENV['PRODUCTION_PASSWORD'];
        break;
    }
  }

  private function generate_token()
  {
    $subject = 'External Call From ' . $_ENV['APP_NAME'];
    $date_created = time();
    $expiration_date = strtotime($_ENV['TOKEN_EXPIRATION']);

    $data = [
      'subject' => $subject,
      'date_created' => $date_created,
      'expiration_date' => $expiration_date,
    ];

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $dataToEncrypt = json_encode($data);
    $encryptedMessage = openssl_encrypt($dataToEncrypt, 'aes-256-cbc', $this->secret_key, 0, $iv);
    return base64_encode($iv . $encryptedMessage);
  }

  public function authenticate_user($credentials)
  {
    if ($this->username == $credentials['data']['username'] && $this->password == $credentials['data']['password']) {

      $generated_token = $this->generate_token();
      $response = [
        'status_code' => 201,
        'status' => 'success',
        'message' => 'Resource Created',
        'description' => 'Token Generated.',
        'data' => [
          'token' => $generated_token
        ]
      ];
      $this->CI->output
        ->set_status_header($response['status_code'])
        ->set_output(json_encode($response));
    } else {
      $response = [
        'status_code' => 400,
        'status' => 'failed',
        'message' => 'Bad Request',
        'description' => 'Username or Password is Incorrect.',
        'data' => []
      ];
      $this->CI->output
        ->set_status_header($response['status_code'])
        ->set_output(json_encode($response));
    }
  }

  public function decrypt_token($encryptedToken)
  {
    // Decode the base64-encoded token
    $decodedToken = base64_decode($encryptedToken);

    // Extract the IV and the encrypted data
    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($decodedToken, 0, $ivSize);
    $encryptedData = substr($decodedToken, $ivSize);

    // Decrypt the data using the secret key and IV
    $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $this->secret_key, 0, $iv);

    // If decryption is successful, return the JSON-decoded data
    if ($decryptedData !== false) {
      $data = json_decode($decryptedData, true);

      // Check if expiration date has passed
      $currentTimestamp = time();
      if (isset($data['expiration_date']) && $data['expiration_date'] >= $currentTimestamp) {
        return true;
      } else {
        // Token has expired
        return false;
      }
    } else {
      // Decryption failed
      return false;
    }
  }

  // public function call_external_api($data)
  // {
  //   $generated_token = $this->generate_token();

  //   $endpoint = $this->endpoint_base_url . $data['endpoint'];
  //   $dataToSend = $data['data'];

  //   $ch = curl_init();
  //   curl_setopt($ch, CURLOPT_URL, $endpoint);
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     'Authorization: Bearer ' . $generated_token,
  //     'Content-Type: application/json',
  //   ]);
  //   curl_setopt($ch, CURLOPT_POST, true);
  //   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToSend));

  //   $response = curl_exec($ch);

  //   if (curl_errno($ch)) {
  //     $error = curl_error($ch);

  //     http_response_code(500);

  //     return [
  //       'status_code' => 500,
  //       'error' => $error
  //     ];
  //   }
  //   curl_close($ch);

  //   return json_decode($response, true);
  // }
}
