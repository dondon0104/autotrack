<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserModel extends Model {
    
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 
        'license_number', 'address', 'is_active',
        'is_verified', 'verification_token', 'verification_expires', 'verified_at'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        // Return single associative array or false
        return $this->db->table($this->table)
                        ->where('email', $email)
                        ->where('is_active', 1)
                        ->get();
    }

    /**
     * Get user by email regardless of verification status
     */
    public function getByEmailAny($email) {
        return $this->db->table($this->table)
                        ->where('email', $email)
                        ->get();
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->where('is_active', 1)
                        ->get();
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        if (!isset($data['is_verified'])) $data['is_verified'] = 0;
        // Use Model::insert which returns last inserted id
        return $this->insert($data);
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }
    
    /**
     * Delete user (soft delete)
     */
    public function deleteUser($id) {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update([
                            'is_active' => 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }
    
    /**
     * Get all active users
     */
    public function getAllUsers($limit = null, $offset = null) {
        $query = $this->db->table($this->table)
                          ->where('is_active', 1)
                          ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($email, $password) {
        $user = $this->getByEmail($email);

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Set email verification token and expiry for a user
     */
    public function setVerificationToken($userId, $token, $expiresAt) {
        return $this->db->table($this->table)
                        ->where('id', (int)$userId)
                        ->update([
                            'verification_token' => $token,
                            'verification_expires' => $expiresAt,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    /**
     * Find by email and token (still active)
     */
    public function findByEmailAndToken($email, $token) {
        return $this->db->table($this->table)
                        ->where('email', $email)
                        ->where('verification_token', $token)
                        ->where('is_active', 1)
                        ->get();
    }

    /**
     * Mark user as verified
     */
    public function markVerified($userId) {
        return $this->db->table($this->table)
                        ->where('id', (int)$userId)
                        ->update([
                            'is_verified' => 1,
                            'verified_at' => date('Y-m-d H:i:s'),
                            'verification_token' => NULL,
                            'verification_expires' => NULL,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }
    
    /**
     * Get user's rental history
     */
    public function getRentalHistory($userId) {
        return $this->db->table('rentals r')
                        ->select('r.*, c.make, c.model, c.year, c.plate_number')
                        ->join('cars c', 'c.id = r.car_id')
                        ->where('r.user_id', $userId)
                        ->order_by('r.created_at', 'DESC')
                        ->get();
    }
}
?>
