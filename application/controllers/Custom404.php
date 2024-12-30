<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom404 extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $this->output->set_status_header('404');

    $authorizationHeader = $this->input->get_request_header('Authorization', true);

    if (!empty($authorizationHeader)) {
      header("Content-Type: application/json");
      echo json_encode(
        [
          'status_code' => 404, 
          'message' => 'Not Found', 
          'description' => 'The page that you are looking for does not exist.'
        ]
      );
    } else {
      header("Content-Type: text/html");
      $this->load->view('custom_404');
    }
  }
}
