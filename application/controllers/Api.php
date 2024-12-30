<?php
defined('BASEPATH') or exit('No direct script access allowed');


require_once(APPPATH . 'services/ApiService.php');
require_once(APPPATH . 'services/ValidationService.php');

class Api extends CI_Controller
{
	private $apiService;
	private $validationService;

	private $response = [];
	private $status_header = null;
	private $date_now = null;
	private $date_now_no_space = null;

	private $status_code = null; // 200, 201 etc.
	private $status = ''; // success/failed
	private $message = ''; // OK, Unauthorized etc.
	private $description = ''; // Missing Post Data, Unauthorized Access etc.
	private $data = []; // array data

	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('Asia/Manila');
		$this->date_now = date('Y-m-d H:i:s');
		$this->date_now_no_space = date('YmdHis');
		$this->output->set_content_type('application/json');

		// MUST: Response Initialization
		$this->response = [
			'status_code' => $this->status_code,
			'status' =>  $this->status,
			'message' =>  $this->message,
			'description' =>  $this->description,
			'data' =>  $this->data
		];

		$this->apiService = new ApiService();
		$this->validationService = new ValidationService();

		$this->load->model('Api_model');

		$fetched_method = $this->router->fetch_method();
		$token_validation_exception = ['generate_token'];

		if (!in_array($fetched_method, $token_validation_exception)) {
			// CHECK AUTHORIZATION
			if (!$this->validate_authorization_header()) {
				exit($this->output->_display());
			}
			if (!$this->validate_token()) {
				exit($this->output->_display());
			}
		}
	}

	private function response()
	{
		$this->response = [
			'status_code' => $this->status_code,
			'status' =>  $this->status,
			'message' =>  $this->message,
			'description' =>  $this->description,
			'data' =>  $this->data
		];

		return $this->response;
	}

	private function response_model($data)
	{
		$this->response = [
			'status_code' => $data['status_code'],
			'status' => $data['status'],
			'message' => $data['message'],
			'description' => $data['description'],
			'data' => isset($data['data']) ? $data['data'] : []
		];

		return $this->response;
	}

	private function validate_token()
	{
		$authorization_header = $this->input->get_request_header('Authorization', true);
		$token = substr($authorization_header, 7);

		$decryption_result = $this->apiService->decrypt_token($token);

		if (!$decryption_result) {
			$this->status_header = 401;
			$this->output->set_status_header($this->status_header);

			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Unauthorized';
			$this->description = 'Invalid or Expired Token.';

			$this->output->set_output(json_encode($this->response()));

			return false;
		}

		return true;
	}

	private function validate_authorization_header()
	{
		$authorization_header = $this->input->get_request_header('Authorization', true);

		if (!$authorization_header || strpos($authorization_header, 'Bearer ') !== 0) {

			$this->status_header = 401;
			$this->output->set_status_header($this->status_header);

			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Unauthorized';
			$this->description = 'Missing or Invalid Bearer Token.';

			$this->output->set_output(json_encode($this->response()));

			return false;
		}
		return true;
	}

	// -------------------------------------------------------------------------------------------------------------------------------------------------------
	private function validate_data_generate_token($data)
	{
		$return = true;

		$required_keys = [
			'username' => [
				'optional_empty' => false,
				'data_type' => 'string',
				'max_length' => 128
			],
			'password' => [
				'optional_empty' => false,
				'data_type' => 'string',
				'max_length' => 128
			]
		];

		if (isset($data['check_endpoint_required_data']) && $data['check_endpoint_required_data'] == true) {
			return $required_keys;
		}

		$validate_data = $this->validationService->simple_validate_data($data, $required_keys);

		if (!empty($validate_data)) {
			$return = false;
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Validation Error: ' . implode(', ', $validate_data);
		}

		$this->output->set_output(json_encode($this->response()));
		return $return;
	}

	public function generate_token()
	{
		$data =  json_decode(file_get_contents('php://input'), true);

		$validate_data_generate_token = $this->validate_data_generate_token($data);

		if ($validate_data_generate_token) {
			$this->apiService->authenticate_user($data);
		}
	}
	// -------------------------------------------------------------------------------------------------------------------------------------------------------

	// -------------------------------------------------------------------------------------------------------------------------------------------------------
	private function validate_data_check_endpoint_required_data($data)
	{
		$return = true;

		$required_keys = [
			'endpoint' => [
				'optional_empty' => false,
				'data_type' => 'string',
				'max_length' => 128
			]
		];

		if (isset($data['check_endpoint_required_data']) && $data['check_endpoint_required_data'] == true) {
			return $required_keys;
		}

		$validate_data = $this->validationService->simple_validate_data($data, $required_keys);

		if (!empty($validate_data)) {
			$return = false;
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Validation Error: ' . implode(', ', $validate_data);
		}

		$this->output->set_output(json_encode($this->response()));
		return $return;
	}

	public function check_endpoint_required_data()
	{
		// VALIDATE CHECK ENDPOINT DATA
		$data = json_decode(file_get_contents('php://input'), true);

		$validate_data_check_endpoint_required_data = $this->validate_data_check_endpoint_required_data($data);

		if ($validate_data_check_endpoint_required_data) {

			$endpoint = $data['data']['endpoint'];

			$method_name = 'validate_data_' . $endpoint;
			$params = [
				'check_endpoint_required_data' => true
			];

			if (method_exists($this, $method_name)) {
				$response['required_keys'] = call_user_func_array([$this, $method_name], [$params]);

				$this->status_header = 200;
				$this->output->set_status_header($this->status_header);
				$this->status_code = $this->status_header;
				$this->status = 'success';
				$this->message = 'OK';
				$this->description = 'Resource Fetched.';
				$this->data = $response;
			} else {
				$this->status_header = 400;
				$this->output->set_status_header($this->status_header);

				$this->status_code = $this->status_header;
				$this->status = 'failed';
				$this->message = 'Bad Request';
				$this->description = 'Endpoint does not exist.';
			}

			$this->output->set_output(json_encode($this->response()));
		}
	}
	// -------------------------------------------------------------------------------------------------------------------------------------------------------

	// -------------------------------------------------------------------------------------------------------------------------------------------------------
	private function validate_data_get_endpoints($data)
	{
		$return = true;

		$required_keys = [
			'user_id' => [
				'optional_empty' => false,
				'data_type' => 'integer',
				'max_length' => 11
			]
		];

		if (isset($data['check_endpoint_required_data']) && $data['check_endpoint_required_data'] == true) {
			return $required_keys;
		}

		$validate_data = $this->validationService->simple_validate_data($data, $required_keys);

		if (!empty($validate_data)) {
			$return = false;
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Validation Error: ' . implode(', ', $validate_data);
		}

		$this->output->set_output(json_encode($this->response()));
		return $return;
	}

	public function get_endpoints()
	{
		// VALIDATE GET ENDPOINTS DATA
		$data = json_decode(file_get_contents('php://input'), true);

		$validate_data_get_endpoints = $this->validate_data_get_endpoints($data);

		if ($validate_data_get_endpoints) {

			$user_id = $data['data']['user_id'];

			$params = [
				'id' => $user_id
			];

			$get_user_details = $this->Api_model->get_user_details($params);

			if ($get_user_details['return']) {

				$controller_name = get_class($this);

				$reflection = new ReflectionClass($controller_name);

				// $public_methods = array();
				// foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				// 	$method_name = $method->getName();

				// 	if (!in_array($method_name, ['__construct', 'get_instance'])) {
				// 		$public_methods[] = $method_name;
				// 	}
				// }

				// sort($public_methods);

				// $response['endpoints'] = $public_methods;
				$response['endpoints'] = [
					'check_endpoint_required_data',
					'generate_token',
					'get_endpoints',
				];

				$this->status_header = 200;
				$this->output->set_status_header($this->status_header);
				$this->status_code = $this->status_header;
				$this->status = 'success';
				$this->message = 'OK';
				$this->description = 'Resource Fetched.';
				$this->data = $response;

				$this->output->set_output(json_encode($this->response()));
			} else {
				// "User does not exist." description in Api_model
				$this->output->set_output(json_encode($this->response_model($get_user_details)));
			}
		}
	}
	// -------------------------------------------------------------------------------------------------------------------------------------------------------

	// -------------------------------------------------------------------------------------------------------------------------------------------------------
	public function questions($id = null)
	{
		$method = $this->input->server('REQUEST_METHOD');

		switch ($method) {
				case 'GET':
						$this->get_question_by_id($id);
						break;
				
				case 'POST':
					$data = json_decode(file_get_contents('php://input'), true);
					$this->create_question($data['data']);
					break;

				case 'PUT':
						echo 'PUT';
						break;
				
				case 'PATCH':
					echo 'PATCH';
					break;

				case 'DELETE':
					echo 'DELETE';
						break;

				default:
						echo 'Method Not Allowed';
						break;
		}
	}

	private function create_question($data)
	{
		if(isset($data) && !empty($data)) {
			$user_id = $data['user_id'];

			// assuming there's already validation for difficulty_id, tags_id and category_id
			// insert the question
			$params = [
				'question_title' => $data['question_details']['question_title'],
				'question' => $data['question_details']['question'],
				'difficulty_id' => $data['question_details']['question_other_details']['difficulty_id'],
				'category_id' => $data['question_details']['question_other_details']['category_id'],
				'question_images' =>  json_encode($data['question_details']['question_images']),
				'modified_by' => $user_id,
				'date_modified' => $this->date_now,
				'created_by' => $user_id,
				'date_created' => $this->date_now
			];
	
			$insert_question = $this->Api_model->insert_question($params);

			if($insert_question['return']) {
				// Map the solutions
				foreach($data['solutions'] as $solution) {
					$params = [
						'question_id' => $insert_question['data']['question_id'],
						'solution_title' => $solution['solution_title'],
						'solution_description' => $solution['solution_description'],
						'solution_sequence' => $solution['solution_sequence'],
						'solution_images' => json_encode($solution['solution_images']),
						'modified_by' => $user_id,
						'date_modified' => $this->date_now,
						'created_by' => $user_id,
						'date_created' => $this->date_now
					];
					$insert_question_solution = $this->Api_model->insert_question_solution($params);

					if($insert_question_solution['return']) {
						// Map the solution steps
						foreach($solution['solution_steps'] as $solution_step) {
							$params = [
								'solution_id' => $insert_question_solution['data']['question_solution_id'],
								'step_title' => $solution_step['step_title'],
								'step_description' => $solution_step['step_description'],
								'step_sequence' => $solution_step['step_sequence'],
								'step_images' => json_encode($solution_step['step_images']),
								'modified_by' => $user_id,
								'date_modified' => $this->date_now,
								'created_by' => $user_id,
								'date_created' => $this->date_now
							];

							$insert_question_solution_step = $this->Api_model->insert_question_solution_step($params);
						}
					}
				}

				// Map the tags
				foreach($data['question_details']['question_other_details']['tags'] as $tags) {
					$params = [
						'question_id' => $insert_question['data']['question_id'],
						'tag_id' => $tags['tag_id'],
						'modified_by' => $user_id,
						'date_modified' => $this->date_now,
						'created_by' => $user_id,
						'date_created' => $this->date_now
					];

					$insert_question_tags_mapping = $this->Api_model->insert_question_tag_mapping($params);
				}

				// Map the answers
				foreach($data['answer_options'] as $answer_options) {
					$params = [
						'question_id' => $insert_question['data']['question_id'],
						'answer' => $answer_options['answer'],
						'is_correct_answer' => $answer_options['is_correct_answer'],
						'answer_images' => json_encode($answer_options['answer_images']),
						'modified_by' => $user_id,
						'date_modified' => $this->date_now,
						'created_by' => $user_id,
						'date_created' => $this->date_now
					];

					$insert_question_answer = $this->Api_model->insert_question_answer($params);
				}

				$this->status_header = 201;
				$this->output->set_status_header($this->status_header);
		
				$this->status_code = $this->status_header;
				$this->status = 'success';
				$this->message = 'Created';
				$this->description = 'Question Created.';

				$this->output->set_output(json_encode($this->response()));
			}
		} else {
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Failed to create question, data must be set and not empty.';

			$this->output->set_output(json_encode($this->response()));
		}
	}

	private function get_question_by_id($id) // UNTESTED
	{
		// No Solid Validation like a boss, just kidding, this is demo come on now, please have mercy, it's just a demo.
		if(isset($id) && !empty($id)) {

			$params = [
				'id' => $id
			];

			$get_question_details = $this->Api_model->get_question_details($params);

			$get_question_details['data']['title'] = stripslashes($get_question_details['data']['question_title']);
			$get_question_details['data']['question'] = stripslashes($get_question_details['data']['question']);
			$get_question_details['data']['question_images'] = json_decode(stripslashes($get_question_details['data']['question_images']), true);

			$this->output->set_output(json_encode($this->response_model($get_question_details)));
		} else {
			$params = [
				'state' => 'ACTIVE'
			];

			$get_questions = $this->Api_model->get_questions($params);

			foreach($get_questions['data'] as &$questions) {
				$questions = (array) $questions;

				$questions['title'] = stripslashes($questions['question_title']);
				$questions['question'] = stripslashes($questions['question']);
				$questions['question_images'] = json_decode(stripslashes($questions['question_images']), true);
			}

			$this->output->set_output(json_encode($this->response_model($get_questions)));
		}
	}
	// -------------------------------------------------------------------------------------------------------------------------------------------------------
} // CLASS CLOSING
