<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_AddContractFields extends Migration {

    public function up() {
        // Add digital contract fields to rentals table
        try {
            $this->db->raw("ALTER TABLE rentals 
                ADD COLUMN contract_signed_at DATETIME NULL AFTER notes,
                ADD COLUMN contract_signature VARCHAR(255) NULL AFTER contract_signed_at,
                ADD COLUMN contract_pdf VARCHAR(255) NULL AFTER contract_signature,
                ADD COLUMN is_contract_signed TINYINT(1) NOT NULL DEFAULT 0 AFTER contract_pdf");
        } catch (Exception $e) {
            // ignore if already added
        }
    }

    public function down() {
        try { $this->db->raw("ALTER TABLE rentals DROP COLUMN is_contract_signed"); } catch (Exception $e) {}
        try { $this->db->raw("ALTER TABLE rentals DROP COLUMN contract_pdf"); } catch (Exception $e) {}
        try { $this->db->raw("ALTER TABLE rentals DROP COLUMN contract_signature"); } catch (Exception $e) {}
        try { $this->db->raw("ALTER TABLE rentals DROP COLUMN contract_signed_at"); } catch (Exception $e) {}
    }
}
