<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_AddGCashPaymentMethod extends Migration {

    public function up() {
        // Add 'gcash' to the ENUM list for payments.payment_method
        $sql = "ALTER TABLE payments 
                MODIFY COLUMN payment_method 
                ENUM('cash','credit_card','debit_card','bank_transfer','gcash') 
                NOT NULL";
        $this->db->raw($sql);
    }

    public function down() {
        // Revert to original list without 'gcash'
        $sql = "ALTER TABLE payments 
                MODIFY COLUMN payment_method 
                ENUM('cash','credit_card','debit_card','bank_transfer') 
                NOT NULL";
        $this->db->raw($sql);
    }
}
