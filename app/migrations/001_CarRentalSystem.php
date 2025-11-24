<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * AutoTrack Car Rental Management System - Database Schema
 * 
 * This migration creates all necessary tables for the car rental system
 */

class Migration_CarRentalSystem extends Migration {
    
    public function up() {
        // Create users table (for customers)
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE,
                'unique' => TRUE
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => TRUE
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ],
            'license_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
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
        $this->dbforge->create_table('users');
        
        // Create cars table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'make' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ],
            'model' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ],
            'year' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => FALSE
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE
            ],
            'plate_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => FALSE,
                'unique' => TRUE
            ],
            'vin' => [
                'type' => 'VARCHAR',
                'constraint' => 17,
                'null' => FALSE,
                'unique' => TRUE
            ],
            'mileage' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ],
            'fuel_type' => [
                'type' => 'ENUM',
                'constraint' => ['gasoline', 'diesel', 'hybrid', 'electric'],
                'default' => 'gasoline'
            ],
            'transmission' => [
                'type' => 'ENUM',
                'constraint' => ['manual', 'automatic'],
                'default' => 'automatic'
            ],
            'seating_capacity' => [
                'type' => 'INT',
                'constraint' => 2,
                'null' => FALSE
            ],
            'daily_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['available', 'rented', 'maintenance', 'out_of_service'],
                'default' => 'available'
            ],
            'image_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => TRUE
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
        $this->dbforge->create_table('cars');
        
        // Create rentals table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'car_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'rental_start' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ],
            'rental_end' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ],
            'actual_return' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ],
            'daily_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'total_days' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ],
            'subtotal' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'tax_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 12.00
            ],
            'tax_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'active', 'completed', 'cancelled'],
                'default' => 'pending'
            ],
            'pickup_location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ],
            'return_location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => TRUE
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
        $this->dbforge->add_key(['user_id', 'car_id']);
        $this->dbforge->create_table('rentals');
        
        // Create payments table
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'rental_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => FALSE
            ],
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'credit_card', 'debit_card', 'bank_transfer'],
                'null' => FALSE
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'completed', 'failed', 'refunded'],
                'default' => 'pending'
            ],
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ],
            'payment_date' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => TRUE
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
        $this->dbforge->add_key(['rental_id', 'user_id']);
        $this->dbforge->create_table('payments');
        
    // Add foreign key constraints (ignore if they already exist)
    try { $this->db->raw('ALTER TABLE rentals ADD CONSTRAINT fk_rentals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'); } catch (Exception $e) {}
    try { $this->db->raw('ALTER TABLE rentals ADD CONSTRAINT fk_rentals_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE'); } catch (Exception $e) {}
    try { $this->db->raw('ALTER TABLE payments ADD CONSTRAINT fk_payments_rental FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE'); } catch (Exception $e) {}
    try { $this->db->raw('ALTER TABLE payments ADD CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'); } catch (Exception $e) {}
        
        // Insert sample data
        $this->insert_sample_data();
    }
    
    public function down() {
        $this->dbforge->drop_table('payments');
        $this->dbforge->drop_table('rentals');
        $this->dbforge->drop_table('cars');
        $this->dbforge->drop_table('users');
    }
    
    private function insert_sample_data() {
        // Insert sample cars
        $cars = [
            [
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => 2023,
                'color' => 'Silver',
                'plate_number' => 'ABC-1234',
                'vin' => '1HGBH41JXMN109186',
                'mileage' => 15000,
                'fuel_type' => 'gasoline',
                'transmission' => 'automatic',
                'seating_capacity' => 5,
                'daily_rate' => 2500.00,
                'status' => 'available',
                'description' => 'Comfortable sedan perfect for city driving',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'make' => 'Honda',
                'model' => 'Civic',
                'year' => 2022,
                'color' => 'White',
                'plate_number' => 'DEF-5678',
                'vin' => '2HGBH41JXMN109187',
                'mileage' => 25000,
                'fuel_type' => 'gasoline',
                'transmission' => 'manual',
                'seating_capacity' => 5,
                'daily_rate' => 2200.00,
                'status' => 'available',
                'description' => 'Sporty compact car with great fuel efficiency',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'make' => 'Ford',
                'model' => 'Explorer',
                'year' => 2023,
                'color' => 'Black',
                'plate_number' => 'GHI-9012',
                'vin' => '3HGBH41JXMN109188',
                'mileage' => 12000,
                'fuel_type' => 'gasoline',
                'transmission' => 'automatic',
                'seating_capacity' => 7,
                'daily_rate' => 3500.00,
                'status' => 'available',
                'description' => 'Spacious SUV ideal for family trips',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        try {
            $stmt = $this->db->raw('SELECT COUNT(*) AS c FROM cars');
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            $count = $row && isset($row['c']) ? (int)$row['c'] : 0;
        } catch (Exception $e) { $count = 0; }

        if ($count === 0) {
            foreach ($cars as $car) {
                $this->db->raw(
                    'INSERT INTO cars (make, model, year, color, plate_number, vin, mileage, fuel_type, transmission, seating_capacity, daily_rate, status, description, created_at) VALUES (:make,:model,:year,:color,:plate,:vin,:mileage,:fuel,:transmission,:seat,:rate,:status,:description,:created_at)',
                    [
                        'make' => $car['make'],
                        'model' => $car['model'],
                        'year' => $car['year'],
                        'color' => $car['color'],
                        'plate' => $car['plate_number'],
                        'vin' => $car['vin'],
                        'mileage' => $car['mileage'],
                        'fuel' => $car['fuel_type'],
                        'transmission' => $car['transmission'],
                        'seat' => $car['seating_capacity'],
                        'rate' => $car['daily_rate'],
                        'status' => $car['status'],
                        'description' => $car['description'],
                        'created_at' => $car['created_at'],
                    ]
                );
            }
        }
    }
}
?>
