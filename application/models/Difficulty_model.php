<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Difficulty_model extends CI_Model
{
	private $table_name = 'difficulties';

	public function __construct()
	{
		parent::__construct();
	}

	public function get_difficulty_details($where_condition)
	{
		$query = $this->db->select('*')
			->from($this->table_name)
			->where($where_condition)
			->get();

		return $query->row();
	}

	public function get_difficulties($where_condition)
	{
		$query = $this->db->select('*')
			->from($this->table_name)
			->where($where_condition)
			->get();

		return $query->result();
	}

	public function update_difficulty($update_fields, $where_condition)
	{
		$this->db->where($where_condition);
		$this->db->update($this->table_name, $update_fields);

		return ($this->db->affected_rows() > 0);
	}

	public function insert_difficulty($insert_fields)
	{
		$this->db->insert($this->table_name, $insert_fields);

		return ($this->db->affected_rows() > 0);
	}
}
