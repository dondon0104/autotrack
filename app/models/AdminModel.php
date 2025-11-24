<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AdminModel extends Model {
    
    protected $table = 'admins';        // ✅ Pangalan ng table sa database
    protected $primary_key = 'id';      // ✅ Primary key ng table

    public function __construct()
    {
        parent::__construct();
    }

    // ✅ Get admin by username
    public function getByUsername($username)
    {
        // Use the Database query builder and return associative array (or false)
        return $this->db->table($this->table)
                        ->where('username', $username)
                        ->get();
    }

    // ✅ Insert new admin (Register)
    public function createAdmin($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
}
