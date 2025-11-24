<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_AddCarCategoryColumn extends Migration {
    private $_lava;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->database();
    }

    public function up() {
        // Add 'category' column to cars if it doesn't exist
        // Use raw SQL for portability similar to other migrations
        try {
            $this->_lava->db->raw("ALTER TABLE cars ADD COLUMN IF NOT EXISTS category VARCHAR(50) NULL AFTER color");
        } catch (Throwable $e) {
            // Some MySQL versions do not support IF NOT EXISTS for ADD COLUMN
            // Perform a defensive check by querying information_schema
            try {
                $stmt = $this->_lava->db->raw("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'category'");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                $exists = $row && isset($row['c']) ? (int)$row['c'] : 0;
                if ($exists === 0) {
                    $this->_lava->db->raw("ALTER TABLE cars ADD COLUMN category VARCHAR(50) NULL AFTER color");
                }
            } catch (Throwable $e2) {
                // Best effort; rethrow original
                throw $e2;
            }
        }
    }

    public function down() {
        try {
            $this->_lava->db->raw("ALTER TABLE cars DROP COLUMN IF EXISTS category");
        } catch (Throwable $e) {
            // Fallback for MySQL without IF EXISTS
            try {
                $stmt = $this->_lava->db->raw("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'category'");
                $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                $exists = $row && isset($row['c']) ? (int)$row['c'] : 0;
                if ($exists === 1) {
                    $this->_lava->db->raw("ALTER TABLE cars DROP COLUMN category");
                }
            } catch (Throwable $e2) {
                // swallow; down is best-effort
            }
        }
    }
}
?>
