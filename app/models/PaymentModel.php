<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class PaymentModel extends Model {
    
    protected $table = 'payments';
    protected $primary_key = 'id';
    protected $fillable = [
        'rental_id', 'user_id', 'amount', 'payment_method', 'payment_status',
        'transaction_id', 'payment_date', 'notes'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get payment by ID
     */
    public function getById($id) {
    return $this->db->table('payments p')
            ->select('p.*, r.id as rental_id, u.first_name, u.last_name, c.make, c.model, c.plate_number')
            ->join('rentals r', 'r.id = p.rental_id')
            ->join('users u', 'u.id = p.user_id')
            ->join('cars c', 'c.id = r.car_id')
            ->where('p.id', $id)
            ->get();
    }
    
    /**
     * Get payments by rental ID
     */
    public function getByRentalId($rentalId) {
        return $this->db->table($this->table)
                        ->where('rental_id', $rentalId)
                        ->order_by('created_at', 'DESC')
                        ->get_all();
    }
    
    /**
     * Get payments by user ID
     */
    public function getByUserId($userId, $limit = null, $offset = null) {
    $query = $this->db->table('payments p')
              ->select('p.*, r.id as rental_id, c.make, c.model, c.plate_number')
              ->join('rentals r', 'r.id = p.rental_id')
              ->join('cars c', 'c.id = r.car_id')
              ->where('p.user_id', $userId)
              ->order_by('p.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Get all payments
     */
    public function getAllPayments($limit = null, $offset = null) {
    $query = $this->db->table('payments p')
              ->select('p.*, r.id as rental_id, u.first_name, u.last_name, c.make, c.model, c.plate_number')
              ->join('rentals r', 'r.id = p.rental_id')
              ->join('users u', 'u.id = p.user_id')
              ->join('cars c', 'c.id = r.car_id')
              ->order_by('p.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get_all();
    }
    
    /**
     * Create new payment
     */
    public function createPayment($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->table($this->table)->insert($data);
    }
    
    /**
     * Update payment
     */
    public function updatePayment($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }
    
    /**
     * Update payment status
     */
    public function updateStatus($id, $status, $transactionId = null) {
        $data = [
            'payment_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }
        
        if ($status === 'completed') {
            $data['payment_date'] = date('Y-m-d H:i:s');
        }
        
        $ok = $this->db->table($this->table)
                ->where('id', $id)
                ->update($data);

        if ($ok && $status === 'completed') {
            // After completion, recalc rental/car statuses based on deposit rules
            $payment = $this->db->table($this->table)->where('id', $id)->get();
            if ($payment) {
                $rental = $this->db->table('rentals')->where('id', $payment['rental_id'])->get();
                if ($rental) {
                    $paidTotal = $this->getPaidTotalByRental($payment['rental_id']);
                    $depositRate = (float) config_item('deposit_rate');
                    $minDeposit = (float) config_item('min_deposit_amount');
                    $allowPartial = (bool) config_item('allow_partial_payments');
                    $totalAmount = (float) $rental['total_amount'];
                    $requiredDeposit = $allowPartial ? max($totalAmount * $depositRate, $minDeposit) : $totalAmount;
                    $newStatus = null;
                    $carStatus = null;
                    if ($paidTotal >= $totalAmount - 0.01) { $newStatus = 'confirmed'; $carStatus = 'rented'; }
                    elseif ($paidTotal >= $requiredDeposit - 0.01) { $newStatus = 'confirmed'; $carStatus = 'rented'; }
                    else { $newStatus = 'pending'; }
                    if ($newStatus) {
                        $this->db->table('rentals')->where('id', $payment['rental_id'])->update(['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                    if ($carStatus) {
                        $this->db->table('cars')->where('id', $rental['car_id'])->update(['status' => $carStatus, 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                }
            }
        }

        return $ok;
    }

    public function getByTransactionId($txnId) {
        return $this->db->table($this->table)->where('transaction_id', $txnId)->get();
    }
    
    /**
     * Process payment
     */
    public function processPayment($rentalId, $amount, $paymentMethod, $transactionId = null) {
    // Use table()->where()->get() to avoid resetting WHERE due to get('table') semantics
    $rental = $this->db->table('rentals')->where('id', $rentalId)->get();
        
        if (!$rental) {
            return false;
        }
        
        $paymentData = [
            'rental_id' => $rentalId,
            'user_id' => $rental['user_id'],
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $paymentId = $this->createPayment($paymentData);
        
        if ($paymentId) {
            // Determine aggregate paid and update statuses accordingly
            $paidTotal = $this->getPaidTotalByRental($rentalId);
            $depositRate = (float) config_item('deposit_rate');
            $minDeposit = (float) config_item('min_deposit_amount');
            $allowPartial = (bool) config_item('allow_partial_payments');

            $totalAmount = (float) $rental['total_amount'];
            $requiredDeposit = $allowPartial ? max($totalAmount * $depositRate, $minDeposit) : $totalAmount;

            $newStatus = null;
            $carStatus = null;

            if ($paidTotal >= $totalAmount - 0.01) { // fully paid
                $newStatus = 'confirmed';
                $carStatus = 'rented';
            } elseif ($paidTotal >= $requiredDeposit - 0.01) { // deposit covered
                $newStatus = 'confirmed';
                $carStatus = 'rented';
            } else {
                // Keep pending until deposit is satisfied
                $newStatus = 'pending';
            }

            if ($newStatus) {
                $this->db->table('rentals')
                    ->where('id', $rentalId)
                    ->update([
                        'status' => $newStatus,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
            if ($carStatus) {
                $this->db->table('cars')
                    ->where('id', $rental['car_id'])
                    ->update([
                        'status' => $carStatus,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
            // After status updates, attempt to auto-complete the rental if conditions are met
            $this->checkAndCompleteRental($rentalId);
        }
        
        return $paymentId;
    }

    /**
     * Check whether a rental should be automatically marked as completed.
     * Criteria: fully paid (paidTotal >= total_amount) AND rental_end <= now.
     * If completed, mark rental.status = 'completed' and free up the car if no other active rentals.
     */
    public function checkAndCompleteRental($rentalId) {
        $rental = $this->db->table('rentals')->where('id', $rentalId)->get();
        if (!$rental) return false;

        $paidTotal = $this->getPaidTotalByRental($rentalId);
        $totalAmount = (float) $rental['total_amount'];

        // Consider floating point tolerance
        if ($paidTotal + 0.01 < $totalAmount) {
            // Not fully paid yet
            return false;
        }

        $now = time();
        $rentalEnd = strtotime($rental['rental_end']);
        if ($rentalEnd === false) return false;

        if ($rentalEnd <= $now) {
            // mark rental as completed
            $this->db->table('rentals')->where('id', $rentalId)->update([
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // If there are no other confirmed/active rentals for this car, free the car
            $other = $this->db->table('rentals')
                        ->where('car_id', $rental['car_id'])
                        ->where('id', '!=', $rentalId)
                        ->where('status', 'confirmed')
                        ->or_where('status', 'active')
                        ->count();

            if (!$other) {
                $this->db->table('cars')->where('id', $rental['car_id'])->update([
                    'status' => 'available',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * Sum of completed payments for a rental
     */
    public function getPaidTotalByRental($rentalId) {
        $row = $this->db->table($this->table)
            ->select_sum('amount', 'amount')
            ->where('rental_id', $rentalId)
            ->where('payment_status', 'completed')
            ->get();
        return (float) ($row['amount'] ?? 0);
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats() {
        $stats = [];
        
    // Total payments
    $stats['total'] = $this->db->table($this->table)->count();

    // Completed payments
    $stats['completed'] = $this->db->table($this->table)
                      ->where('payment_status', 'completed')
                      ->count();

    // Pending payments
    $stats['pending'] = $this->db->table($this->table)
                    ->where('payment_status', 'pending')
                    ->count();

    // Failed payments
    $stats['failed'] = $this->db->table($this->table)
                    ->where('payment_status', 'failed')
                    ->count();
        
    // Total amount collected
    $totalAmount = $this->db->table($this->table)
                ->select_sum('amount', 'amount')
                ->where('payment_status', 'completed')
                ->get();
    $stats['total_amount'] = $totalAmount['amount'] ?? 0;

    // Payment method breakdown
    $methods = $this->db->table($this->table)
                ->select('payment_method, COUNT(*) as count, SUM(amount) as total')
                ->where('payment_status', 'completed')
                ->group_by('payment_method')
                ->get_all();

    $stats['by_method'] = $methods;
        
        return $stats;
    }
    
    /**
     * Get recent payments
     */
    public function getRecentPayments($limit = 10) {
        return $this->db->table('payments p')
                        ->select('p.*, u.first_name, u.last_name, c.make, c.model, c.plate_number')
                        ->join('rentals r', 'r.id = p.rental_id')
                        ->join('users u', 'u.id = p.user_id')
                        ->join('cars c', 'c.id = r.car_id')
                        ->order_by('p.created_at', 'DESC')
                        ->limit($limit)
                        ->get_all();
    }
}
?>
