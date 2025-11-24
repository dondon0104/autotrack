<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class CarModel extends Model {
    
    protected $table = 'cars';
    protected $primary_key = 'id';
    protected $fillable = [
        'make', 'model', 'year', 'color', 'plate_number', 'vin', 'mileage',
        'fuel_type', 'transmission', 'seating_capacity', 'daily_rate', 
        'status', 'image_path', 'description', 'category'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get car by ID
     */
    public function getById($id) {
        // Use table()->where()->get() to preserve WHERE and fetch the correct record
        return $this->db->table($this->table)->where('id', $id)->get();
    }
    
    /**
     * Get all cars
     */
    public function getAllCars($limit = null, $offset = null) {
        $query = $this->db->table($this->table)->order_by('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Get available cars
     */
    public function getAvailableCars($limit = null, $offset = null) {
        $query = $this->db->table($this->table)
                          ->where('status', 'available')
                          ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Create new car
     */
    public function createCar($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->table($this->table)->insert($data);
    }
    
    /**
     * Update car
     */
    public function updateCar($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        // Ensure table is set before updating
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }
    
    /**
     * Delete car
     */
    public function deleteCar($id) {
        // Ensure table is set before deleting
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->delete();
    }
    
    /**
     * Update car status
     */
    public function updateStatus($id, $status) {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update([
                            'status' => $status,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }
    
    /**
     * Search cars by criteria
     */
    public function searchCars($criteria = []) {
        $query = $this->db->table($this->table);

        // Text filters (safe with LIKE)
        if (!empty($criteria['make'] ?? '')) {
            $query->like('make', $criteria['make']);
        }
        if (!empty($criteria['model'] ?? '')) {
            $query->like('model', $criteria['model']);
        }

        // Exact match filters
        if (isset($criteria['year']) && $criteria['year'] !== '' && is_numeric($criteria['year'])) {
            $query->where('year', (int)$criteria['year']);
        }
        if (!empty($criteria['fuel_type'] ?? '')) {
            $query->where('fuel_type', $criteria['fuel_type']);
        }
        if (!empty($criteria['transmission'] ?? '')) {
            $query->where('transmission', $criteria['transmission']);
        }

        // Always constrain availability for customer search when requested
        if (!empty($criteria['available_only'])) {
            $query->where('status', 'available');
        }

        // Fetch first, then apply numeric range filters in PHP to avoid SQL operator quirks
        $rows = $query->order_by('daily_rate', 'ASC')->get_all();

        // Guard if DB returns non-array
        if (!is_array($rows)) { return []; }

        $min = null; $max = null; $seat = null;
        if (isset($criteria['min_price']) && $criteria['min_price'] !== '' && is_numeric($criteria['min_price'])) {
            $min = (float)$criteria['min_price'];
        }
        if (isset($criteria['max_price']) && $criteria['max_price'] !== '' && is_numeric($criteria['max_price'])) {
            $max = (float)$criteria['max_price'];
        }
        if (isset($criteria['seating_capacity']) && $criteria['seating_capacity'] !== '' && is_numeric($criteria['seating_capacity'])) {
            $seat = (int)$criteria['seating_capacity'];
        }

        if ($min !== null) {
            $rows = array_values(array_filter($rows, function($r) use ($min){ return (float)($r['daily_rate'] ?? 0) >= $min; }));
        }
        if ($max !== null) {
            $rows = array_values(array_filter($rows, function($r) use ($max){ return (float)($r['daily_rate'] ?? 0) <= $max; }));
        }
        if ($seat !== null) {
            $rows = array_values(array_filter($rows, function($r) use ($seat){ return (int)($r['seating_capacity'] ?? 0) >= $seat; }));
        }

        return $rows;
    }
    
    /**
     * Check if car is available for rental period
     */
    public function isAvailableForPeriod($carId, $startDate, $endDate) {
    $conflicts = $this->db->table('rentals')
                 ->select('id')
                 ->where('car_id', $carId)
                 ->in('status', ['confirmed', 'active'])
                 ->where('rental_start', '<=', $endDate)
                 ->where('rental_end', '>=', $startDate)
                 ->get();
        
        return empty($conflicts);
    }
    
    /**
     * Get car statistics
     */
    public function getCarStats() {
        $stats = [];
        
        // Total cars
        $stats['total'] = $this->db->table($this->table)->count();
        
        // Available cars
        $stats['available'] = $this->db->table($this->table)
                                      ->where('status', 'available')
                                      ->count();
        
        // Rented cars
        $stats['rented'] = $this->db->table($this->table)
                                   ->where('status', 'rented')
                                   ->count();
        
        // Maintenance cars
        $stats['maintenance'] = $this->db->table($this->table)
                                         ->where('status', 'maintenance')
                                         ->count();
        
        return $stats;
    }
}
?>
