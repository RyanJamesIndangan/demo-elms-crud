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

    $this->load->model('Category_model');
    $this->load->model('Difficulty_model');
    $this->load->model('Question_model');
    $this->load->model('Question_solution_model');
    $this->load->model('Question_solution_step_model');
    $this->load->model('Question_tag_mapping_model');
    $this->load->model('Tag_model');
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
    $get_user_types = $this->User_type_model->get_user_types($data);

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

  // QUESTIONS
  public function get_question_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION NOT EXIST
    $get_question_details = $this->Question_model->get_question_details($data);

    $description = 'No Question Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_details != null) {
      $description = 'Question Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_questions($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTIONS NOT EXIST
    $get_questions = $this->Question_model->get_questions($data);

    $description = 'No Questions Found.';
    $this->return = false;
    $this->data = [];

    if ($get_questions != null) {
      $description = 'Questions Fetched.';

      $this->return = true;
      $this->data = (array) $get_questions;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function insert_question($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // INSERT QUESTION
    $insert_question = $this->Question_model->insert_question($data);

    if (!$insert_question) {
      // failed to insert question
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to insert question, please contact the support and try again later.';
      $this->data = [];

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 201;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'Created';
    $this->description = 'Question Created.';
    $this->data = [
      'question_id' => $this->db->insert_id(),
      'question_title' => $data['question_title'],
      'question' => $data['question'],
    ];

    $this->return = true;
    return $this->response();
  }
} // CLASS CLOSING
