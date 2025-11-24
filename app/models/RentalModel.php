<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class RentalModel extends Model {
    
    protected $table = 'rentals';
    protected $primary_key = 'id';
    protected $fillable = [
        'user_id', 'car_id', 'rental_start', 'rental_end', 'actual_return',
        'daily_rate', 'total_days', 'subtotal', 'tax_rate', 'tax_amount',
        'total_amount', 'status', 'pickup_location', 'return_location', 'notes',
        'contract_signed_at', 'contract_signature', 'contract_pdf', 'is_contract_signed'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get rental by ID
     */
    public function getById($id) {
    return $this->db->table('rentals r')
            ->select('r.*, u.first_name, u.last_name, u.email, u.phone, c.make, c.model, c.year, c.plate_number')
            ->join('users u', 'u.id = r.user_id')
            ->join('cars c', 'c.id = r.car_id')
            ->where('r.id', $id)
            ->get();
    }
    
    /**
     * Get all rentals
     */
    public function getAllRentals($limit = null, $offset = null) {
    $query = $this->db->table('rentals r')
              ->select('r.*, u.first_name, u.last_name, u.email, c.make, c.model, c.year, c.plate_number')
              ->join('users u', 'u.id = r.user_id')
              ->join('cars c', 'c.id = r.car_id')
              ->order_by('r.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Get rentals by user ID
     */
    public function getByUserId($userId, $limit = null, $offset = null) {
    $query = $this->db->table('rentals r')
              ->select('r.*, c.make, c.model, c.year, c.plate_number')
              ->join('cars c', 'c.id = r.car_id')
              ->where('r.user_id', $userId)
              ->order_by('r.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Create new rental
     */
    public function createRental($data) {
        // Calculate rental details
        $startDate = new DateTime($data['rental_start']);
        $endDate = new DateTime($data['rental_end']);
        $totalDays = $startDate->diff($endDate)->days + 1;
        
        $data['total_days'] = $totalDays;
        $data['subtotal'] = $data['daily_rate'] * $totalDays;
        $data['tax_amount'] = $data['subtotal'] * ($data['tax_rate'] / 100);
        $data['total_amount'] = $data['subtotal'] + $data['tax_amount'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->table($this->table)->insert($data);
    }
    
    /**
     * Update rental
     */
    public function updateRental($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }
    
    /**
     * Update rental status
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
     * Complete rental (mark as returned)
     */
    public function completeRental($id, $actualReturn = null) {
        $data = [
            'status' => 'completed',
            'actual_return' => $actualReturn ?: date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }
    
    /**
     * Cancel rental
     */
    public function cancelRental($id) {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update([
                            'status' => 'cancelled',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }
    
    /**
     * Get rental statistics
     */
    public function getRentalStats() {
        $stats = [];

        $now = date('Y-m-d H:i:s');

        // Total rentals
        $stats['total'] = $this->db->table($this->table)->count();

        // Pending rentals
        $stats['pending'] = $this->db->table($this->table)
                                    ->where('status', 'pending')
                                    ->count();

        // Confirmed rentals (future or ongoing but not completed/cancelled)
        $stats['confirmed'] = $this->db->table($this->table)
                                      ->where('status', 'confirmed')
                                      ->count();

    // Active rentals:
    //  - Any with status = 'active' (explicit override)
    //  - Any with status = 'confirmed' and current time within [rental_start, rental_end]
    $activeExplicit = $this->db->table($this->table)
                   ->where('status', 'active')
                   ->count();
    $activeWindow = $this->db->table($this->table)
                  ->where('status', 'confirmed')
                  ->where('rental_start', '<=', $now)
                  ->where('rental_end', '>=', $now)
                  ->count();
    $stats['active'] = (int)$activeExplicit + (int)$activeWindow;

        // Completed rentals
        $stats['completed'] = $this->db->table($this->table)
                                      ->where('status', 'completed')
                                      ->count();

        // Cancelled rentals
        $stats['cancelled'] = $this->db->table($this->table)
                                      ->where('status', 'cancelled')
                                      ->count();

        // Total revenue (from completed rentals)
        $revenue = $this->db->select_sum('total_amount')
                            ->where('status', 'completed')
                            ->get($this->table);
        $stats['total_revenue'] = $revenue['total_amount'] ?? 0;

        return $stats;
    }
    
    /**
     * Get recent rentals
     */
    public function getRecentRentals($limit = 10) {
    return $this->db->table('rentals r')
            ->select('r.*, u.first_name, u.last_name, c.make, c.model, c.plate_number')
            ->join('users u', 'u.id = r.user_id')
            ->join('cars c', 'c.id = r.car_id')
            ->order_by('r.created_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }
    
    /**
     * Check for rental conflicts
     */
    public function checkConflicts($carId, $startDate, $endDate, $excludeId = null) {
        $query = $this->db->table($this->table)
                          ->select('id')
                          ->where('car_id', $carId)
                          ->in('status', ['confirmed', 'active'])
                          ->where('rental_start', '<=', $endDate)
                          ->where('rental_end', '>=', $startDate);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->get_all();
    }
}
?>
