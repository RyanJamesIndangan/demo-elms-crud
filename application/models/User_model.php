<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
	private $table_name = 'users';

	public function __construct()
	{
		parent::__construct();
	}

	public function get_user_details($where_condition)
	{
		$query = $this->db->select('*')
			->from($this->table_name)
			->where($where_condition)
			->get();

		return $query->row();
	}

	public function get_users($where_condition)
	{
		$query = $this->db->select('*')
			->from($this->table_name)
			->where($where_condition)
			->get();

		return $query->result();
	}

	public function update_user($update_fields, $where_condition)
	{
		$this->db->where($where_condition);
		$this->db->update($this->table_name, $update_fields);

		return ($this->db->affected_rows() > 0);
	}

	public function insert_user($insert_fields)
	{
		$this->db->insert($this->table_name, $insert_fields);

		return ($this->db->affected_rows() > 0);
	}

	public function validate_password($password, $hashed_password)
	{
		return password_verify($password, $hashed_password);
	}
	
	public function validate_pin($pin, $hashed_pin)
	{
		return password_verify($pin, $hashed_pin);
	}

}
