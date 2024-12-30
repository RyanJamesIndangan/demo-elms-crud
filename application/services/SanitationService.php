<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SanitationService
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    public function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } elseif (is_bool($data)) {
            // Convert boolean to integer (1 for true, 0 for false)
            $data = ($data === true) ? 1 : 0;
        } elseif (is_int($data) || is_float($data)) {
            // Numbers don't need escaping, so keep them as is
        } else {
            // Sanitize strings and other data types
            $data = $this->CI->db->escape_str($data);
        }

        return $data;
    }
}
