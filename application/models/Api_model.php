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
    $this->load->model('Question_answer_model');
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

  // QUESTION ANSWERS
  public function get_question_answer_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION NOT EXIST
    $get_question_answer_details = $this->Question_answer_model->get_question_answer_details($data);

    $description = 'No Question Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_answer_details != null) {
      $description = 'Question Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_answer_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_question_answers($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION ANSWERS NOT EXIST
    $get_question_answers = $this->Question_answer_model->get_question_answers($data);

    $description = 'No Questions Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_answers != null) {
      $description = 'Question Answers Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_answers;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function insert_question_answer($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // INSERT QUESTION ANSWER
    $insert_question_answer = $this->Question_answer_model->insert_question_answer($data);

    if (!$insert_question_answer) {
      // failed to insert question answer
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to insert question answer, please contact the support and try again later.';
      $this->data = [];

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 201;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'Created';
    $this->description = 'Question Answer Created.';
    $this->data = [
      'question_answer_id' => $this->db->insert_id(),
      'answer' => $data['answer']
    ];

    $this->return = true;
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
      'question' => $data['question']
    ];

    $this->return = true;
    return $this->response();
  }

  public function patch_question($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // UPDATE QUESTION TABLE
    $update_fields = [
      'question' => $data['question'],
      'modified_by' => $data['user_id']
    ];

    $where_condition = [
      'id' => $data['question_id']
    ];

    $patch_question = $this->Question_model->update_question($update_fields, $where_condition);

    if (!$patch_question) {
      // failed to patch question table
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to patch question, please contact the support and try again later.';

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = 'Question Patched.';

    $this->return = true;
    return $this->response();
  }

  public function delete_question($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // DELETE ROW IN QUESTION TABLE
    $where_condition = [
      'id' => $data['question_id']
    ];

    $delete_question = $this->Question_model->delete_question($where_condition);

    if (!$delete_question) {
      // failed to delete question table
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to delete question, please contact the support and try again later.';

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = 'Question Deleted.';

    $this->return = true;
    return $this->response();
  }

  // QUESTION SOLUTION
  public function get_question_solution_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION SOLUTION NOT EXIST
    $get_question_solution_details = $this->Question_solution_model->get_question_solution_details($data);

    $description = 'No Question Solution Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_solution_details != null) {
      $description = 'Question Solution Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_solution_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_question_solutions($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION SOLUTIONS NOT EXIST
    $get_question_solutions = $this->Question_solution_model->get_question_solutions($data);

    $description = 'No Question Solutions Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_solutions != null) {
      $description = 'Question Solutions Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_solutions;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function insert_question_solution($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // INSERT QUESTION
    $insert_question_solution = $this->Question_solution_model->insert_question_solution($data);

    if (!$insert_question_solution) {
      // failed to insert question solution
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to insert question solution, please contact the support and try again later.';
      $this->data = [];

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 201;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'Created';
    $this->description = 'Question Solution Created.';
    $this->data = [
      'question_solution_id' => $this->db->insert_id(),
      'question_solution_title' => $data['solution_title'],
      'question_solution_description' => $data['solution_description']
    ];

    $this->return = true;
    return $this->response();
  }

  // QUESTION SOLUTION STEP
  public function get_question_solution_step_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION SOLUTION NOT EXIST
    $get_question_solution_step_details = $this->Question_solution_step_model->get_question_solution_step_details($data);

    $description = 'No Question Solution Step Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_solution_step_details != null) {
      $description = 'Question Solution Step Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_solution_step_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_question_solution_steps($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION SOLUTIONS NOT EXIST
    $get_question_solution_steps = $this->Question_solution_step_model->get_question_solution_steps($data);

    $description = 'No Question Solution Steps Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_solution_steps != null) {
      $description = 'Question Solutions Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_solution_steps;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function insert_question_solution_step($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // INSERT QUESTION
    $insert_question_solution_step = $this->Question_solution_step_model->insert_question_solution_step($data);

    if (!$insert_question_solution_step) {
      // failed to insert question solution step
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to insert question solution step, please contact the support and try again later.';
      $this->data = [];

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 201;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'Created';
    $this->description = 'Question Solution Created.';
    $this->data = [
      'question_solution_step_id' => $this->db->insert_id(),
      'question_solution_step_title' => $data['step_title'],
      'question_solution_step_description' => $data['step_description']
    ];

    $this->return = true;
    return $this->response();
  }

  // QUESTION TAG MAPPING
  public function get_question_tag_mapping_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION TAG MAPPING NOT EXIST
    $get_question_tag_mapping_details = $this->Question_tag_mapping_model->get_question_tag_mapping_details($data);

    $description = 'No Question Tag Mapping Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_tag_mapping_details != null) {
      $description = 'Question Tag Mapping Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_tag_mapping_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_question_tag_mappings($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK QUESTION TAG MAPPINGS NOT EXIST
    $get_question_tag_mappings = $this->Question_tag_mapping_model->get_question_tag_mappings($data);

    $description = 'No Question Tag Mappings Found.';
    $this->return = false;
    $this->data = [];

    if ($get_question_tag_mappings != null) {
      $description = 'Question Tag Mappings Fetched.';

      $this->return = true;
      $this->data = (array) $get_question_tag_mappings;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function insert_question_tag_mapping($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // INSERT QUESTION TAG MAPPING
    $insert_question_tag_mapping = $this->Question_tag_mapping_model->insert_question_tag_mapping($data);

    if (!$insert_question_tag_mapping) {
      // failed to insert question tag mapping
      $this->status_header = 500;
      $this->output->set_status_header($this->status_header);

      $this->status_code = $this->status_header;
      $this->status = 'failed';
      $this->message = 'Internal Server Error';
      $this->description = 'Failed to insert question tag mapping, please contact the support and try again later.';
      $this->data = [];

      $this->return = false;
      return $this->response();
    }

    $this->status_header = 201;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'Created';
    $this->description = 'Question Tag Mapping Created.';
    $this->data = [
      'question_tag_mapping_id' => $this->db->insert_id(),
      'question_id' => $data['question_id'],
      'tag_id' => $data['tag_id']
    ];

    $this->return = true;
    return $this->response();
  }

  // CATEGORIES
  public function get_category_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK CATEGORY NOT EXIST
    $get_category_details = $this->Category_model->get_category_details($data);

    $description = 'No Category Found.';
    $this->return = false;
    $this->data = [];

    if ($get_category_details != null) {
      $description = 'Category Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_category_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_categories($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK CATEGORIES NOT EXIST
    $get_categories = $this->Category_model->get_categories($data);

    $description = 'No Categories Found.';
    $this->return = false;
    $this->data = [];

    if ($get_categories != null) {
      $description = 'Categories Fetched.';

      $this->return = true;
      $this->data = (array) $get_categories;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  // DIFFICULTIES
  public function get_difficulty_details($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK DIFFICULTY NOT EXIST
    $get_difficulty_details = $this->Difficulty_model->get_difficulty_details($data);

    $description = 'No Difficulty Found.';
    $this->return = false;
    $this->data = [];

    if ($get_difficulty_details != null) {
      $description = 'Difficulty Details Fetched.';

      $this->return = true;
      $this->data = (array) $get_difficulty_details;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

  public function get_difficulties($data)
  {
    $data = $this->sanitationService->sanitize($data);

    // CHECK DIFFICULTIES NOT EXIST
    $get_difficulties = $this->Difficulty_model->get_difficulties($data);

    $description = 'No Difficulties Found.';
    $this->return = false;
    $this->data = [];

    if ($get_difficulties != null) {
      $description = 'Difficulties Fetched.';

      $this->return = true;
      $this->data = (array) $get_difficulties;
    }

    $this->status_header = 200;
    $this->output->set_status_header($this->status_header);

    $this->status_code = $this->status_header;
    $this->status = 'success';
    $this->message = 'OK';
    $this->description = $description;

    return $this->response();
  }

    // TAGS
    public function get_tag_details($data)
    {
      $data = $this->sanitationService->sanitize($data);
  
      // CHECK TAG NOT EXIST
      $get_tag_details = $this->Tag_model->get_tag_details($data);
  
      $description = 'No Tag Found.';
      $this->return = false;
      $this->data = [];
  
      if ($get_tag_details != null) {
        $description = 'Tag Details Fetched.';
  
        $this->return = true;
        $this->data = (array) $get_tag_details;
      }
  
      $this->status_header = 200;
      $this->output->set_status_header($this->status_header);
  
      $this->status_code = $this->status_header;
      $this->status = 'success';
      $this->message = 'OK';
      $this->description = $description;
  
      return $this->response();
    }
  
    public function get_tags($data)
    {
      $data = $this->sanitationService->sanitize($data);
  
      // CHECK TAGS NOT EXIST
      $get_tags = $this->Tag_model->get_tags($data);
  
      $description = 'No Tags Found.';
      $this->return = false;
      $this->data = [];
  
      if ($get_tags != null) {
        $description = 'Tags Fetched.';
  
        $this->return = true;
        $this->data = (array) $get_tags;
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
