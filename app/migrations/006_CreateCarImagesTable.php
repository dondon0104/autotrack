<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Migration_CreateCarImagesTable extends Migration {

    public function up() {
        // Define table structure
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'car_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ],
            'image_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ],
            'is_primary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ]
        ]);

        $this->dbforge->add_key('id', TRUE);
        // Optional: add FK constraint to cars(id)
        $this->dbforge->add_foreign_key('car_id', 'cars', 'id', 'CASCADE', 'CASCADE');
        $this->dbforge->create_table('car_images');

        // Add helpful indexes (color for lookups)
        try { $this->db->raw('CREATE INDEX IF NOT EXISTS idx_car_images_car_id ON car_images (car_id)'); } catch (Exception $e) {}
        try { $this->db->raw('CREATE INDEX IF NOT EXISTS idx_car_images_color ON car_images (color)'); } catch (Exception $e) {}
    }

    public function down() {
        // Drop table
        $this->dbforge->drop_table('car_images');
    }
}
?>
