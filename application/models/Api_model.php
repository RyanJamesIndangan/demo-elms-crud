<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . 'services/SanitationService.php');
class Api_Model extends CI_Model
{
  private $sanitationService;

  private $return = null; // true/false

  private $response = [];
  private $status_header = null;

  private $status_code = null; // 200, 201 etc.
  private $status = ''; // success/failed
  private $message = ''; // OK, Unauthorized etc.
  private $description = ''; // Missing Post Data, Unauthorized Access etc.
  private $data = []; // array data

  public function __construct()
  {
    parent::__construct();

    $this->sanitationService = new SanitationService();

    $this->response = [
      'status_code' => $this->status_code,
      'status' =>  $this->status,
      'message' =>  $this->message,
      'description' =>  $this->description,
      'data' =>  $this->data
    ];

    $this->load->model('User_model');
    $this->load->model('User_type_model');
  }

  private function response()
  {
    $this->response = [
      'return' => $this->return,
      'status_code' => $this->status_code,
      'status' =>  $this->status,
      'message' =>  $this->message,
      'description' =>  $this->description,
      'data' =>  $this->data
    ];

    return $this->response;
  }

  // users -------------------------------------------------------------------------------------------------------------------------------------------------------
  public function get_users($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK USERS NOT EXIST
    $get_users = $this->User_model->get_users($data);

    $description = 'No Users Found.';
    $this->return = false;
    $this->data = [];

    if ($get_users != null) {
      $description = 'Users Fetched.';

      $this->return = true;
      $this->data = (array) $get_users;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_user_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK USER NOT EXIST
    $get_user_details = $this->User_model->get_user_details($data);

    if (!$get_user_details) {
      $this->status_header = 400;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Bad Request';
      $this->description = 'No User Found.';

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = 'User Details Fetched.';

    // Casting to Array to avoid object problems
    $this->data = (array) $get_user_details;

    $this->return = true;
    return $this->response();
  }

  // USER TYPE
  public function get_user_type_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK USER TYPE NOT EXIST
    $get_user_type_details = $this->User_type_model->get_user_type_details($data);

    $description = 'No User Type Found.';
    $this->return = false;
    $this->data = [];

    if ($get_user_type_details != null) {
      $description = 'User Type Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_user_type_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_user_types($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK USER TYPE NOT EXIST
    $get_user_types = $this->User_type_model->get_networks($data);

    $description = 'No User Types Found.';
    $this->return = false;
    $this->data = [];

    if ($get_user_types != null) {
      $description = 'User` Types Fetched.';

      $this->return = true;
      $this->data = (array) $get_user_types;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }
} // CLASS CLOSING
