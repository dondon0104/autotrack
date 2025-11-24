<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_AddUpdatedAtToCarImages extends Migration {
    private $_lava;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->database();
    }

    public function up() {
        try {
            $this->_lava->db->raw("ALTER TABLE car_images ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL");
        } catch (Throwable $e) {
            // Fallback for MySQL versions without IF NOT EXISTS
            try {
                $stmt = $this->_lava->db->raw("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_images' AND COLUMN_NAME = 'updated_at'");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                $exists = $row && isset($row['c']) ? (int)$row['c'] : 0;
                if ($exists === 0) {
                    $this->_lava->db->raw("ALTER TABLE car_images ADD COLUMN updated_at DATETIME NULL");
                }
            } catch (Throwable $e2) {
                throw $e2;
            }
        }
    }

    public function down() {
        try {
            $this->_lava->db->raw("ALTER TABLE car_images DROP COLUMN IF EXISTS updated_at");
        } catch (Throwable $e) {
            try {
                $stmt = $this->_lava->db->raw("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'car_images' AND COLUMN_NAME = 'updated_at'");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                $exists = $row && isset($row['c']) ? (int)$row['c'] : 0;
                if ($exists === 1) {
                    $this->_lava->db->raw("ALTER TABLE car_images DROP COLUMN updated_at");
                }
            } catch (Throwable $e2) {
                // ignore
            }
        }
    }
}
?>
