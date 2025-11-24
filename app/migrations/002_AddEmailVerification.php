<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_AddEmailVerification extends Migration {
    private $_lava;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->database();
    }

    public function up() {
        // Add email verification columns to users table
        // is_verified: 0/1 flag
        // verification_token: token string
        // verification_expires: expiration datetime
        // verified_at: datetime when verified
        
        // Add columns if not exists
        $this->_lava->db->raw("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        $this->_lava->db->raw("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) NULL AFTER is_verified");
        $this->_lava->db->raw("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_expires DATETIME NULL AFTER verification_token");
        $this->_lava->db->raw("ALTER TABLE users ADD COLUMN IF NOT EXISTS verified_at DATETIME NULL AFTER verification_expires");
        // Optional index for faster lookups
        $this->_lava->db->raw("CREATE INDEX IF NOT EXISTS idx_users_verification_token ON users (verification_token)");
    }

    public function down() {
        // Remove columns (if supported by your DB engine)
        $this->_lava->db->raw("ALTER TABLE users DROP COLUMN IF EXISTS verified_at");
        $this->_lava->db->raw("ALTER TABLE users DROP COLUMN IF EXISTS verification_expires");
        $this->_lava->db->raw("ALTER TABLE users DROP COLUMN IF EXISTS verification_token");
        $this->_lava->db->raw("ALTER TABLE users DROP COLUMN IF EXISTS is_verified");
    }
}
?>
