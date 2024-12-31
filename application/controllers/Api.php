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
				$this->get_questions($id);
				break;

			case 'POST':
				$data = json_decode(file_get_contents('php://input'), true);
				$this->create_question($data['data']);
				break;

			// case 'PUT':
			// 	echo 'PUT';
			// 	break;

			case 'PATCH':
				$data = json_decode(file_get_contents('php://input'), true);
				$data['data']['question_id'] = $id;
				$this->patch_question($data['data']);
				break;

			case 'DELETE':
				$data = json_decode(file_get_contents('php://input'), true);
				$data['data']['question_id'] = $id;
				$this->delete_question($data['data']);
				break;

			default:
				echo 'Method Not Allowed';
				break;
		}
	}

	private function create_question($data)
	{
		if (isset($data) && !empty($data)) {
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

			if ($insert_question['return']) {
				// Map the solutions
				foreach ($data['solutions'] as $solution) {
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

					if ($insert_question_solution['return']) {
						// Map the solution steps
						foreach ($solution['solution_steps'] as $solution_step) {
							$params = [
								'solution_id' => $insert_question_solution['data']['question_solution_id'],
								'step_title' => $solution_step['step_title'],
								'step_description' => $solution_step['step_description'],
								'step_result' => $solution_step['step_result'],
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
				foreach ($data['question_details']['question_other_details']['tags'] as $tags) {
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
				foreach ($data['answer_options'] as $answer_options) {
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

	private function get_questions($id)
	{
		// No Solid Validation like a boss, just kidding, this is demo come on now, please have mercy, it's just a demo.
		if (isset($id) && !empty($id)) {
			$this->get_question_by_id($id);
		} else {
			$this->get_questions_all();
		}
	}

	private function get_question_by_id($id)
	{
		$response = [];

		$params = [
			'id' => $id
		];

		$get_question_details = $this->Api_model->get_question_details($params);

		if($get_question_details['return']) {

			$get_question_details['data']['title'] = stripslashes($get_question_details['data']['question_title']);
			$get_question_details['data']['question'] = stripslashes($get_question_details['data']['question']);
			$get_question_details['data']['question_images'] = json_decode(stripslashes($get_question_details['data']['question_images']), true);

			// Sample Question Template in JSON
			// Get Solution (will only fetch 1 row since we are using "get_question_solution_details" and not "get_question_solutions")
			$params = [
				'question_id' => $id
			];

			$get_solution_details = $this->Api_model->get_question_solution_details($params);

			if ($get_solution_details['return']) {
				$get_solution_details['data']['solution_title'] = stripslashes($get_solution_details['data']['solution_title']);
				$get_solution_details['data']['solution_description'] = stripslashes($get_solution_details['data']['solution_description']);
				$get_solution_details['data']['solution_images'] = json_decode(stripslashes($get_solution_details['data']['solution_images']), true);

				// Get Solution Steps
				$steps = [];

				$params = [
					'solution_id' => $get_solution_details['data']['id']
				];

				$get_solution_steps = $this->Api_model->get_question_solution_steps($params);

				foreach ($get_solution_steps['data'] as &$solution_steps) {
					$solution_steps = (array) $solution_steps;

					$solution_steps['step_title'] = stripslashes($solution_steps['step_title']);
					$solution_steps['step_description'] = stripslashes($solution_steps['step_description']);
					$solution_steps['step_result'] = stripslashes($solution_steps['step_result']);
					$solution_steps['step_images'] = json_decode(stripslashes($solution_steps['step_images']), true);

					$steps[] = [
						'Title' => $solution_steps['step_title'],
						'step_description' => $solution_steps['step_description'],
						'step_result' => $solution_steps['step_result'],
						'ImageUrl' => $solution_steps['step_images']
					];
				}

				// Get Answers
				$correct_answer = '';
				$options = [];

				$params = [
					'question_id' => $id
				];

				$get_question_answers = $this->Api_model->get_question_answers($params);

				if ($get_question_answers['return']) {
					foreach ($get_question_answers['data'] as &$question_answers) {
						$question_answers = (array) $question_answers;

						$question_answers['answer'] = stripslashes($question_answers['answer']);
						$question_answers['answer_images'] = json_decode(stripslashes($question_answers['answer_images']), true);

						if ($question_answers['is_correct_answer'] == 'true') {
							$correct_answer = $question_answers['answer'];
						}

						$options[] = [
							'answer' => $question_answers['answer']
						];
					}

					// $get_solution_details['data']['solution_steps'] = $get_solution_steps;
					$get_solution_details['data']['Steps'] = $get_solution_steps;
					$response = [
						'id' => $get_question_details['data']['id'],
						'Question' => $get_question_details['data']['question'],
						'Solution' => $get_solution_details['data']['solution_description'],
						'CorrectAnswer' => $correct_answer,
						'Options' => $options,
						'Steps' => $steps,
						'ImageUrl' => $get_question_details['data']['question_images']
					];


					// $get_question_details['data']['solution'] = $get_solution_details['data'];
				}
			}

			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'Resource Fetched.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		}	else {
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Question ID does not exist.';

			$this->output->set_output(json_encode($this->response()));
		}
	}

	private function get_questions_all()
	{
		$response = [];

		$params = [
			'state' => 'ACTIVE'
		];

		$get_questions = $this->Api_model->get_questions($params);

		if($get_questions['return']) {

			foreach ($get_questions['data'] as &$get_questions) {
				$get_questions = (array) $get_questions;

				$get_questions['title'] = stripslashes($get_questions['question_title']);
				$get_questions['question'] = stripslashes($get_questions['question']);
				$get_questions['question_images'] = json_decode(stripslashes($get_questions['question_images']), true);

				// Sample Question Template in JSON
				// Get Solution (will only fetch 1 row since we are using "get_question_solution_details" and not "get_question_solutions")
				$params = [
					'question_id' => $get_questions['id']
				];

				$get_solution_details = $this->Api_model->get_question_solution_details($params);

				if ($get_solution_details['return']) {
					$get_solution_details['data']['solution_title'] = stripslashes($get_solution_details['data']['solution_title']);
					$get_solution_details['data']['solution_description'] = stripslashes($get_solution_details['data']['solution_description']);
					$get_solution_details['data']['solution_images'] = json_decode(stripslashes($get_solution_details['data']['solution_images']), true);

					// Get Solution Steps
					$steps = [];

					$params = [
						'solution_id' => $get_solution_details['data']['id']
					];

					$get_solution_steps = $this->Api_model->get_question_solution_steps($params);

					foreach ($get_solution_steps['data'] as &$solution_steps) {
						$solution_steps = (array) $solution_steps;

						$solution_steps['step_title'] = stripslashes($solution_steps['step_title']);
						$solution_steps['step_description'] = stripslashes($solution_steps['step_description']);
						$solution_steps['step_result'] = stripslashes($solution_steps['step_result']);
						$solution_steps['step_images'] = json_decode(stripslashes($solution_steps['step_images']), true);

						$steps[] = [
							'Title' => $solution_steps['step_title'],
							'step_description' => $solution_steps['step_description'],
							'step_result' => $solution_steps['step_result'],
							'ImageUrl' => $solution_steps['step_images']
						];
					}

					// Get Answers
					$correct_answer = '';
					$options = [];

					$params = [
						'question_id' => $get_questions['id']
					];

					$get_question_answers = $this->Api_model->get_question_answers($params);

					if ($get_question_answers['return']) {
						foreach ($get_question_answers['data'] as &$question_answers) {
							$question_answers = (array) $question_answers;

							$question_answers['answer'] = stripslashes($question_answers['answer']);
							$question_answers['answer_images'] = json_decode(stripslashes($question_answers['answer_images']), true);

							if ($question_answers['is_correct_answer'] == 'true') {
								$correct_answer = $question_answers['answer'];
							}

							$options[] = [
								'answer' => $question_answers['answer']
							];
						}

						$get_solution_details['data']['Steps'] = $get_solution_steps;
						$response[] = [
							'id' => $get_questions['id'],
							'Question' => $get_questions['question'],
							'Solution' => $get_solution_details['data']['solution_description'],
							'CorrectAnswer' => $correct_answer,
							'Options' => $options,
							'Steps' => $steps,
							'ImageUrl' => $get_questions['question_images']
						];
					}
				}
			}

			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'Resource Fetched.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		} else {
			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'No Questions Found.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		}
	}

	public function questions_v2($id = null)
	{
		$method = $this->input->server('REQUEST_METHOD');

		switch ($method) {
			case 'GET':
				$this->get_questions_v2($id);
				break;

			case 'POST':
				echo 'POST';
				// $data = json_decode(file_get_contents('php://input'), true);
				// $this->create_question_v2($data['data']);
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

	private function get_questions_v2($id)
	{
		// No Solid Validation like a boss, just kidding, this is demo come on now, please have mercy, it's just a demo.
		if (isset($id) && !empty($id)) {
			$this->get_question_by_id_v2($id);
		} else {
			$this->get_questions_all_v2();
		}
	}

	private function get_question_by_id_v2($id)
	{
		$response = [];

		$question_details = [];

		$params = [
			'id' => $id
		];

		$get_question_details = $this->Api_model->get_question_details($params);

		if ($get_question_details['return']) {
			$get_question_details['data']['title'] = stripslashes($get_question_details['data']['question_title']);
			$get_question_details['data']['question'] = stripslashes($get_question_details['data']['question']);
			$get_question_details['data']['question_images'] = json_decode(stripslashes($get_question_details['data']['question_images']), true);

			$question_details = [
				'id' => $get_question_details['data']['id'],
				'question_title' => $get_question_details['data']['question_title'],
				'question' => $get_question_details['data']['question'],
			];

			$question_other_details = [];

			// Get Category Details
			$params = [
				'id' => $get_question_details['data']['category_id']
			];
			$get_category_details = $this->Api_model->get_category_details($params);

			// Get Difficulty Details
			$params = [
				'id' => $get_question_details['data']['difficulty_id']
			];
			$get_difficulty_details = $this->Api_model->get_difficulty_details($params);

			// Get Question Tags Mapping
			$params = [
				'question_id' => $get_question_details['data']['id']
			];
			$get_question_tag_mappings = $this->Api_model->get_question_tag_mappings($params);

			if ($get_question_tag_mappings['return']) {
				foreach ($get_question_tag_mappings['data'] as &$question_tag_mappings) {
					$question_tag_mappings = (array) $question_tag_mappings;

					// Get Tag Details
					$params = [
						'id' => $question_tag_mappings['tag_id'],
					];
					$get_tag_details = $this->Api_model->get_tag_details($params);
					if ($get_tag_details['return']) {
						$get_tag_details['data']['tag'] = stripslashes($get_tag_details['data']['tag']);
						$get_tag_details['data']['tag_description'] = stripslashes($get_tag_details['data']['description']);
						$get_tag_details['data']['tag_remarks'] = stripslashes($get_tag_details['data']['remarks']);

						$question_tag_mappings['tag_details'] = $get_tag_details['data'];
					} else {
						$question_tag_mappings['tag_details'] = [];
					}
				}
			}
			$question_other_details = [
				'category_details' => (isset($get_category_details['data']) ? $get_category_details['data'] : []),
				'difficulty_details' => (isset($get_difficulty_details['data']) ? $get_difficulty_details['data'] : []),
				'tags' => (isset($get_question_tag_mappings['data']) ? $get_question_tag_mappings['data'] : [])
			];

			$question_details['question_other_details'] = $question_other_details;
			$question_details['question_images'] = (isset($get_question_details['data']['question_images']) ? $get_question_details['data']['question_images'] : []);

			$response['question_details'] = $question_details;

			$params = [
				'question_id' => $id
			];

			$get_solutions = $this->Api_model->get_question_solutions($params);

			if ($get_solutions['return']) {
				foreach ($get_solutions['data'] as &$get_solutions) {

					$get_solutions = (array) $get_solutions;

					$get_solutions['solution_title'] = stripslashes($get_solutions['solution_title']);
					$get_solutions['solution_description'] = stripslashes($get_solutions['solution_description']);
					$get_solutions['solution_images'] = json_decode(stripslashes($get_solutions['solution_images']), true);

					// Get Solution Steps
					$steps = [];

					$params = [
						'solution_id' => $get_solutions['id']
					];

					$get_solution_steps = $this->Api_model->get_question_solution_steps($params);

					foreach ($get_solution_steps['data'] as &$solution_steps) {
						$solution_steps = (array) $solution_steps;

						$solution_steps['step_title'] = stripslashes($solution_steps['step_title']);
						$solution_steps['step_description'] = stripslashes($solution_steps['step_description']);
						$solution_steps['step_result'] = stripslashes($solution_steps['step_result']);
						$solution_steps['step_images'] = json_decode(stripslashes($solution_steps['step_images']), true);

						$steps[] = [
							'step_title' => $solution_steps['step_title'],
							'step_description' => $solution_steps['step_description'],
							'step_result' => $solution_steps['step_result'],
							'step_sequence' => $solution_steps['step_result'],
							'step_images' => $solution_steps['step_images']
						];
					}

					$get_solutions['solution_steps'] = $steps;
				}
			}

			$response['solutions'] = isset($get_solutions) ? $get_solutions : [];

			// Get Answer Options
			$answer_options_response = [];
			$params = [
				'question_id' => $id
			];

			$get_question_answers = $this->Api_model->get_question_answers($params);

			if ($get_question_answers['return']) {
				foreach ($get_question_answers['data'] as &$question_answers) {
					$question_answers = (array) $question_answers;

					$question_answers['answer'] = stripslashes($question_answers['answer']);
					$question_answers['answer_images'] = json_decode(stripslashes($question_answers['answer_images']), true);

					$answer_options_response[] = $question_answers;
				}
			}

			$response['answer_options'] = $answer_options_response;

			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'Resource Fetched.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		} else {
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Question ID does not exist.';

			$this->output->set_output(json_encode($this->response()));
		}
	}

	private function get_questions_all_v2()
	{
		$response = [];

		$question_details = [];

		$params = [
			'STATE' => 'ACTIVE'
		];

		$get_questions = $this->Api_model->get_questions($params);

		if ($get_questions['return']) {
			$response_compiler = [];

			foreach ($get_questions['data'] as &$get_questions_array) {
				$get_questions_array = (array) $get_questions_array;

				$get_questions_array['title'] = stripslashes($get_questions_array['question_title']);
				$get_questions_array['question'] = stripslashes($get_questions_array['question']);
				$get_questions_array['question_images'] = json_decode(stripslashes($get_questions_array['question_images']), true);

				$question_details = [
					'id' => $get_questions_array['id'],
					'question_title' => $get_questions_array['question_title'],
					'question' => $get_questions_array['question'],
				];

				$question_other_details = [];

				// Get Category Details
				$params = [
					'id' => $get_questions_array['category_id']
				];
				$get_category_details = $this->Api_model->get_category_details($params);

				// Get Difficulty Details
				$params = [
					'id' => $get_questions_array['difficulty_id']
				];
				$get_difficulty_details = $this->Api_model->get_difficulty_details($params);

				// Get Question Tags Mapping
				$params = [
					'question_id' => $get_questions_array['id']
				];
				$get_question_tag_mappings = $this->Api_model->get_question_tag_mappings($params);

				if ($get_question_tag_mappings['return']) {
					foreach ($get_question_tag_mappings['data'] as &$question_tag_mappings) {
						$question_tag_mappings = (array) $question_tag_mappings;

						// Get Tag Details
						$params = [
							'id' => $question_tag_mappings['tag_id'],
						];
						$get_tag_details = $this->Api_model->get_tag_details($params);
						if ($get_tag_details['return']) {
							$get_tag_details['data']['tag'] = stripslashes($get_tag_details['data']['tag']);
							$get_tag_details['data']['tag_description'] = stripslashes($get_tag_details['data']['description']);
							$get_tag_details['data']['tag_remarks'] = stripslashes($get_tag_details['data']['remarks']);

							$question_tag_mappings['tag_details'] = $get_tag_details['data'];
						} else {
							$question_tag_mappings['tag_details'] = [];
						}
					}
				}
				$question_other_details = [
					'category_details' => (isset($get_category_details['data']) ? $get_category_details['data'] : []),
					'difficulty_details' => (isset($get_difficulty_details['data']) ? $get_difficulty_details['data'] : []),
					'tags' => (isset($get_question_tag_mappings['data']) ? $get_question_tag_mappings['data'] : [])
				];

				$question_details['question_other_details'] = $question_other_details;
				$question_details['question_images'] = (isset($get_questions_array['question_images']) ? $get_questions_array['question_images'] : []);

				$response_compiler['question_details'] = $question_details;

				$params = [
					'question_id' => $get_questions_array['id']
				];

				$get_solutions = $this->Api_model->get_question_solutions($params);

				if ($get_solutions['return']) {
					foreach ($get_solutions['data'] as &$get_solutions) {

						$get_solutions = (array) $get_solutions;

						$get_solutions['solution_title'] = stripslashes($get_solutions['solution_title']);
						$get_solutions['solution_description'] = stripslashes($get_solutions['solution_description']);
						$get_solutions['solution_images'] = json_decode(stripslashes($get_solutions['solution_images']), true);

						// Get Solution Steps
						$steps = [];

						$params = [
							'solution_id' => $get_solutions['id']
						];

						$get_solution_steps = $this->Api_model->get_question_solution_steps($params);

						foreach ($get_solution_steps['data'] as &$solution_steps) {
							$solution_steps = (array) $solution_steps;

							$solution_steps['step_title'] = stripslashes($solution_steps['step_title']);
							$solution_steps['step_description'] = stripslashes($solution_steps['step_description']);
							$solution_steps['step_result'] = stripslashes($solution_steps['step_result']);
							$solution_steps['step_images'] = json_decode(stripslashes($solution_steps['step_images']), true);

							$steps[] = [
								'step_title' => $solution_steps['step_title'],
								'step_description' => $solution_steps['step_description'],
								'step_result' => $solution_steps['step_result'],
								'step_sequence' => $solution_steps['step_result'],
								'step_images' => $solution_steps['step_images']
							];
						}

						$get_solutions['solution_steps'] = $steps;
					}
				}

				$response_compiler['solutions'] = isset($get_solutions) ? $get_solutions : [];

				// Get Answer Options
				$answer_options_response = [];
				$params = [
					'question_id' => $get_questions_array['id']
				];

				$get_question_answers = $this->Api_model->get_question_answers($params);

				if ($get_question_answers['return']) {
					foreach ($get_question_answers['data'] as &$question_answers) {
						$question_answers = (array) $question_answers;

						$question_answers['answer'] = stripslashes($question_answers['answer']);
						$question_answers['answer_images'] = json_decode(stripslashes($question_answers['answer_images']), true);

						$answer_options_response[] = $question_answers;
					}
				}

				$response_compiler['answer_options'] = $answer_options_response;

				$response[] = $response_compiler;
			}

			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'Resource Fetched.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		} else {
			$this->status_header = 200;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'success';
			$this->message = 'OK';
			$this->description = 'No Questions Found.';
			$this->data = $response;

			$this->output->set_output(json_encode($this->response()));
		}
	}

	private function patch_question($data)
	{
		if (isset($data) && !empty($data)) {
			// Check Question ID if existing
			$params = [
				'id' => $data['question_id']
			];

			$get_question_details = $this->Api_model->get_question_details($params);

			if($get_question_details['return']) {
				// Patch Question
				$params = [
					'user_id' => $data['user_id'],
					'question_id' => $data['question_id'],
					'question' => $data['question'],
				];

				$patch_question = $this->Api_model->patch_question($params);

				$id = $data['question_id'];
				$old_question = $get_question_details['data']['question'];
				$new_question = $data['question'];

				$patch_question['data'] = [];

				$patch_question['data'] = [
					'id' => $id,
					'old_question' => $old_question,
					'new_question' => $new_question
				];

				$this->output->set_output(json_encode($this->response_model($patch_question)));
			} else {
				$this->status_header = 400;
				$this->output->set_status_header($this->status_header);
				$this->status_code = $this->status_header;
				$this->status = 'failed';
				$this->message = 'Bad Request';
				$this->description = 'Question ID does not exist.';

				$this->output->set_output(json_encode($this->response()));
			}
		} else {
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Failed to patch question, data must be set and not empty.';

			$this->output->set_output(json_encode($this->response()));
		}
	}

	private function delete_question($data)
	{
		if (isset($data) && !empty($data)) {
			// Check Question ID if existing
			$params = [
				'id' => $data['question_id']
			];

			$get_question_details = $this->Api_model->get_question_details($params);

			if($get_question_details['return']) {
				// Delete Question
				$params = [
					'user_id' => $data['user_id'],
					'question_id' => $data['question_id']
				];

				$delete_question = $this->Api_model->delete_question($params);

				$this->output->set_output(json_encode($this->response_model($delete_question)));
			} else {
				$this->status_header = 400;
				$this->output->set_status_header($this->status_header);
				$this->status_code = $this->status_header;
				$this->status = 'failed';
				$this->message = 'Bad Request';
				$this->description = 'Question ID does not exist.';

				$this->output->set_output(json_encode($this->response()));
			}
		} else {
			$this->status_header = 400;
			$this->output->set_status_header($this->status_header);
			$this->status_code = $this->status_header;
			$this->status = 'failed';
			$this->message = 'Bad Request';
			$this->description = 'Failed to delete question, data must be set and not empty.';

			$this->output->set_output(json_encode($this->response()));
		}
	}
	// -------------------------------------------------------------------------------------------------------------------------------------------------------
} // CLASS CLOSING
