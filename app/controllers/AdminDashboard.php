<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AdminDashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Load required models
        $this->call->model('CarModel');
        $this->call->model('RentalModel');
        $this->call->model('PaymentModel');
        $this->call->model('UserModel');
    $this->call->model('CarImageModel');
    }

    /**
     * Admin dashboard - show statistics and recent activities
     */
    public function index()
    {
        $this->requireAdmin();

        // Get statistics
        $carStats = $this->CarModel->getCarStats();
        $rentalStats = $this->RentalModel->getRentalStats();
        $paymentStats = $this->PaymentModel->getPaymentStats();

        // Get recent activities
        $recentRentals = $this->RentalModel->getRecentRentals(5);
        $recentPayments = $this->PaymentModel->getRecentPayments(5);

        $data = [
            'username' => $this->session->userdata('username'),
            'car_stats' => $carStats,
            'rental_stats' => $rentalStats,
            'payment_stats' => $paymentStats,
            'recent_rentals' => $recentRentals,
            'recent_payments' => $recentPayments
        ];

        $this->call->view('admin/dashboard', $data);
    }

    /**
     * Show all cars
     */
    public function cars()
    {
        $this->requireAdmin();

        $cars = $this->CarModel->getAllCars();
        $stats = $this->CarModel->getCarStats();
        
        $data = [
            'cars' => $cars,
            'username' => $this->session->userdata('username'),
            'total_cars' => $stats['total'] ?? 0,
            'available_cars' => $stats['available'] ?? 0,
            'rented_cars' => $stats['rented'] ?? 0,
            'maintenance_cars' => $stats['maintenance'] ?? 0
        ];

        $this->call->view('admin/cars', $data);
    }

    /**
     * Show add car form
     */
    public function addCar()
    {
        $this->requireAdmin();

        $data = [
            'username' => $this->session->userdata('username')
        ];

        $this->call->view('admin/add_car', $data);
    }

    /**
     * Process add car
     */
    public function processAddCar()
    {
        $this->requireAdmin();
        try {
            $make = trim($this->io->post('make'));
        $model = trim($this->io->post('model'));
        $year = $this->io->post('year');
        $color = trim($this->io->post('color'));
        $category = trim($this->io->post('category'));
        $plateNumber = trim($this->io->post('plate_number'));
        $vin = trim($this->io->post('vin'));
        $mileage = $this->io->post('mileage');
        $fuelType = $this->io->post('fuel_type');
        $transmission = $this->io->post('transmission');
        $seatingCapacity = $this->io->post('seating_capacity');
        $dailyRate = $this->io->post('daily_rate');
    $description = trim($this->io->post('description'));
    $imageUrlInput = trim($this->io->post('image_path'));

        if (empty($make) || empty($model) || empty($year) || empty($color) || empty($plateNumber) || empty($vin) || empty($mileage) || empty($fuelType) || empty($transmission) || empty($seatingCapacity) || empty($dailyRate)) {
            $this->session->set_flashdata('error', 'All required fields must be filled.');
            redirect('/admin/cars/add');
        }

        // Normalize plate number: remove non-alphanumeric characters and uppercase
        $plateNumberRaw = $plateNumber;
        $plateNumber = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plateNumberRaw));

        // Check if plate number already exists (normalized comparison removing hyphens/spaces)
        $stmt = $this->db->raw(
            "SELECT COUNT(*) AS cnt FROM cars WHERE REPLACE(UPPER(plate_number), '-', '') = ? OR REPLACE(UPPER(plate_number), ' ', '') = ?",
            [$plateNumber, $plateNumber]
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $existingCount = isset($row['cnt']) ? (int)$row['cnt'] : 0;
        if ($existingCount > 0) {
            $this->session->set_flashdata('error', 'A car with plate number ' . $plateNumberRaw . ' is already registered in the system.');
            redirect('/admin/cars/add');
        }

        // Format VIN (remove spaces and convert to uppercase)
        $vin = trim(strtoupper(preg_replace('/\s+/', '', $vin)));
        
        // Debug: Check table existence and records
    $stmt = $this->db->raw("SHOW TABLES LIKE 'cars'");
    $checkTable = $stmt->rowCount();
    if ($checkTable == 0) {
            $this->session->set_flashdata('error', 'Database table not found. Please run database migrations.');
            redirect('/admin/cars/add');
        }

        // Get total number of cars
        $stmt = $this->db->raw("SELECT COUNT(*) AS cnt FROM cars");
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCars = isset($countRow['cnt']) ? (int)$countRow['cnt'] : 0;
        if ($totalCars == 0) {
            // No cars in database, skip duplicate check
            // Continue (there's no duplicate possible)
        }

        // Check if VIN exists with modified query
        $stmt = $this->db->raw("SELECT id FROM cars WHERE vin = ?", [$vin]);
        $existingVin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingVin) {
            $this->session->set_flashdata('error', 'A car with VIN ' . $vin . ' is already registered in the system.');
            redirect('/admin/cars/add');
        }

        // Handle image upload if provided
        $finalImagePath = '';
        if (isset($_FILES['car_image']) && is_array($_FILES['car_image']) && !empty($_FILES['car_image']['name'])) {
            // Ensure uploads directory is usable
            $uploadsDirFs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cars';
            // Use directory helper to create if needed
            $this->call->helper('directory');
            if (is_dir_usable($uploadsDirFs)) {
                // Use Upload library
                $this->call->library('Upload');
                                $u = new Upload($_FILES['car_image']);
                                // Configure upload (discrete calls to avoid chaining type confusion)
                                $u->is_image();
                                $u->allowed_extensions(['jpg','jpeg','png','gif']);
                                $u->allowed_mimes(['image/jpg','image/jpeg','image/png','image/gif']);
                                $u->max_size(5); // 5MB
                                $u->set_dir($uploadsDirFs);
                                $u->encrypt_name();

                if ($u->do_upload()) {
                    $savedName = $u->get_filename();
                    // Build public URL path using site_url and relative path
                    $finalImagePath = site_url('/public/uploads/cars/' . $savedName);
                } else {
                    $errors = $u->get_errors();
                    if (!empty($errors)) {
                        $this->session->set_flashdata('error', 'Image upload failed: ' . implode('; ', $errors));
                        redirect('/admin/cars/add');
                    }
                }
            } else {
                $this->session->set_flashdata('error', 'Upload directory is not writable.');
                redirect('/admin/cars/add');
            }
        }

        // Fallback to user-provided URL if no upload succeeded and input URL provided
        if (empty($finalImagePath) && !empty($imageUrlInput)) {
            $finalImagePath = $imageUrlInput;
        }

        $carData = [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'category' => $category,
            'plate_number' => $plateNumber,
            'vin' => $vin,
            'mileage' => $mileage,
            'fuel_type' => $fuelType,
            'transmission' => $transmission,
            'seating_capacity' => $seatingCapacity,
            'daily_rate' => $dailyRate,
            'description' => $description,
            'image_path' => $finalImagePath,
            'status' => 'available'
        ];

        $carId = $this->CarModel->createCar($carData);

        if ($carId) {
            $this->session->set_flashdata('success', 'Car added successfully.');
            redirect('/admin/cars');
        } else {
            $this->session->set_flashdata('error', 'Failed to add car. Please try again.');
            redirect('/admin/cars/add');
        }
        } catch (\Throwable $e) {
            // Log full exception to runtime/error.log for debugging
            $logPath = __DIR__ . '/../../runtime/error.log';
            $msg = "[" . date('Y-m-d H:i:s') . "] Exception in AdminDashboard::processAddCar: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n";
            @file_put_contents($logPath, $msg, FILE_APPEND | LOCK_EX);
            // Provide a friendly error to the user
            $this->session->set_flashdata('error', 'An internal error occurred. Debug info was logged.');
            redirect('/admin/cars/add');
        }
    }

    /**
     * Show edit car form
     */
    public function editCar($carId)
    {
        $this->requireAdmin();

        $car = $this->CarModel->getById($carId);
        
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/admin/cars');
        }

        $data = [
            'car' => $car,
            'username' => $this->session->userdata('username')
        ];

        $this->call->view('admin/edit_car', $data);
    }

    /**
     * Process edit car
     */
    public function processEditCar()
    {
        $this->requireAdmin();

        $carId = $this->io->post('car_id');
        // Use string defaults to avoid null issues
        $make = trim((string)$this->io->post('make'));
        $model = trim((string)$this->io->post('model'));
        $year = $this->io->post('year');
        $color = trim((string)$this->io->post('color'));
        $plateNumber = trim((string)$this->io->post('plate_number'));
        $vin = trim((string)$this->io->post('vin'));
        $mileage = $this->io->post('mileage') ?? '';
        $fuelType = $this->io->post('fuel_type') ?? '';
        $transmission = $this->io->post('transmission') ?? '';
        $seatingCapacity = $this->io->post('seating_capacity') ?? '';
        $dailyRate = $this->io->post('daily_rate');
        $status = $this->io->post('status') ?? 'available';
        $description = trim((string)$this->io->post('description'));

        // Only enforce fields that are present in the edit form
        if (empty($make) || empty($model) || empty($year) || empty($plateNumber) || empty($vin) || empty($dailyRate)) {
            $this->session->set_flashdata('error', 'All required fields must be filled.');
            redirect('/admin/cars/edit/' . $carId);
        }

        // Normalize plate and VIN for comparison
        $plateNorm = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plateNumber));
        $vin = trim(strtoupper(preg_replace('/\s+/', '', $vin)));

        // Check plate duplicate excluding current car
        $stmt = $this->db->raw(
            "SELECT COUNT(*) AS cnt FROM cars WHERE (REPLACE(UPPER(plate_number), '-', '') = ? OR REPLACE(UPPER(plate_number), ' ', '') = ?) AND id != ?",
            [$plateNorm, $plateNorm, $carId]
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($row['cnt']) && (int)$row['cnt'] > 0) {
            $this->session->set_flashdata('error', 'Plate number already exists.');
            redirect('/admin/cars/edit/' . $carId);
        }

        // Check VIN duplicate excluding current car
        $stmt = $this->db->raw("SELECT id FROM cars WHERE vin = ? AND id != ?", [$vin, $carId]);
        $existingVin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingVin) {
            $this->session->set_flashdata('error', 'VIN already exists.');
            redirect('/admin/cars/edit/' . $carId);
        }

        $carData = [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'category' => $this->io->post('category'),
            'plate_number' => $plateNumber,
            'vin' => $vin,
            'mileage' => $mileage,
            'fuel_type' => $fuelType,
            'transmission' => $transmission,
            'seating_capacity' => $seatingCapacity,
            'daily_rate' => $dailyRate,
            'status' => $status,
            'description' => $description
        ];

        // If setting to maintenance/out_of_service, ensure there are no active/confirmed rentals
        if (in_array($status, ['maintenance', 'out_of_service'])) {
            $conflictCount = $this->db->table('rentals')
                                ->where('car_id', $carId)
                                ->in('status', ['confirmed', 'active'])
                                ->count();
            if ($conflictCount) {
                $this->session->set_flashdata('error', 'Cannot mark car as "' . htmlspecialchars($status) . '" while there are active or confirmed rentals. Please resolve those rentals first or use the force-maintenance action.');
                redirect('/admin/cars/edit/' . $carId);
            }
        }

        $result = $this->CarModel->updateCar($carId, $carData);

        if ($result) {
            $this->session->set_flashdata('success', 'Car updated successfully.');
            redirect('/admin/cars');
        } else {
            $this->session->set_flashdata('error', 'Failed to update car. Please try again.');
            redirect('/admin/cars/edit/' . $carId);
        }
    }

    /**
     * Delete car
     */
    public function deleteCar($carId)
    {
        $this->requireAdmin();

        $car = $this->CarModel->getById($carId);
        
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/admin/cars');
        }

        // Check if car has active rentals
        $stmt = $this->db->raw("SELECT id FROM rentals WHERE car_id = ? AND status IN ('confirmed','active')", [$carId]);
        $activeRentals = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($activeRentals)) {
            $this->session->set_flashdata('error', 'Cannot delete car with active rentals.');
            redirect('/admin/cars');
        }

        $result = $this->CarModel->deleteCar($carId);

        if ($result) {
            $this->session->set_flashdata('success', 'Car deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete car.');
        }

        redirect('/admin/cars');
    }

    /**
     * Show all rentals
     */
    public function rentals()
    {
        $this->requireAdmin();

        $rentals = $this->RentalModel->getAllRentals();
        $rentalStats = $this->RentalModel->getRentalStats();
        
        $data = [
            'rentals' => $rentals,
            'username' => $this->session->userdata('username'),
            'rental_stats' => $rentalStats,
        ];

        $this->call->view('admin/rentals', $data);
    }

    /**
     * View single rental details
     */
    public function viewRental($rentalId)
    {
        $this->requireAdmin();

        $rentalId = (int) $rentalId;
        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental) {
            $this->session->set_flashdata('error', 'Rental not found.');
            redirect('/admin/rentals');
        }

        // Fetch related payments
        $payments = $this->PaymentModel->getByRentalId($rentalId);

        // Compute paid total and required deposit
        $paidTotal = $this->PaymentModel->getPaidTotalByRental($rentalId);
        $totalAmount = (float) ($rental['total_amount'] ?? 0);
        $depositRate = (float) config_item('deposit_rate');
        $minDeposit = (float) config_item('min_deposit_amount');
        $allowPartial = (bool) config_item('allow_partial_payments');
        $requiredDeposit = $allowPartial ? max($totalAmount * $depositRate, $minDeposit) : $totalAmount;

        $data = [
            'rental' => $rental,
            'payments' => $payments,
            'paid_total' => $paidTotal,
            'required_deposit' => $requiredDeposit,
            'username' => $this->session->userdata('username'),
        ];

        $this->call->view('admin/rental_view', $data);
    }

    /**
     * Confirm a rental if valid (no conflicts, deposit ok, contract signed)
     */
    public function confirmRental($rentalId)
    {
        $this->requireAdmin();
        $rentalId = (int) $rentalId;

        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental) { $this->session->set_flashdata('error', 'Rental not found.'); redirect('/admin/rentals'); }

        // Only allow confirming from pending state
        if (!in_array(strtolower($rental['status']), ['pending','confirmed'])) {
            $this->session->set_flashdata('error', 'Rental cannot be confirmed from current status.');
            redirect('/admin/rentals/view/' . $rentalId);
        }

        // Ensure contract signed (if your flow requires it before confirm)
        if (empty($rental['is_contract_signed'])) {
            $this->session->set_flashdata('error', 'Contract must be signed before confirming the rental.');
            redirect('/admin/rentals/view/' . $rentalId);
        }

        // Check deposit/payment threshold
        $paidTotal = $this->PaymentModel->getPaidTotalByRental($rentalId);
        $totalAmount = (float) ($rental['total_amount'] ?? 0);
        $depositRate = (float) config_item('deposit_rate');
        $minDeposit = (float) config_item('min_deposit_amount');
        $allowPartial = (bool) config_item('allow_partial_payments');
        $requiredDeposit = $allowPartial ? max($totalAmount * $depositRate, $minDeposit) : $totalAmount;
        if ($paidTotal + 0.01 < $requiredDeposit) { // allow minor float epsilon
            $this->session->set_flashdata('error', 'Insufficient deposit. Required at least ₱' . number_format($requiredDeposit, 2));
            redirect('/admin/rentals/view/' . $rentalId);
        }

        // Check for schedule conflicts
        $conflicts = $this->RentalModel->checkConflicts(
            $rental['car_id'],
            $rental['rental_start'],
            $rental['rental_end'],
            $rentalId
        );
        if (!empty($conflicts)) {
            $this->session->set_flashdata('error', 'Schedule conflict detected for this car.');
            redirect('/admin/rentals/view/' . $rentalId);
        }

        // Update rental and car status
        $this->RentalModel->updateStatus($rentalId, 'confirmed');
        $this->db->table('cars')->where('id', $rental['car_id'])->update([
            'status' => 'rented',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->session->set_flashdata('success', 'Rental confirmed.');
        redirect('/admin/rentals/view/' . $rentalId);
    }

    /**
     * Cancel a rental
     */
    public function cancelRental($rentalId)
    {
        $this->requireAdmin();
        $rentalId = (int) $rentalId;
        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental) { $this->session->set_flashdata('error', 'Rental not found.'); redirect('/admin/rentals'); }

        // Allow cancel from pending/confirmed/active
        if (!in_array(strtolower($rental['status']), ['pending','confirmed','active'])) {
            $this->session->set_flashdata('error', 'Rental cannot be cancelled from current status.');
            redirect('/admin/rentals/view/' . $rentalId);
        }

        $this->RentalModel->cancelRental($rentalId);

        // If no other active/confirmed rentals exist for this car, mark it available
        $other = $this->db->table('rentals')
            ->where('car_id', $rental['car_id'])
            ->where('id', '!=', $rentalId)
            ->in('status', ['confirmed','active'])
            ->count();
        if ((int)$other === 0) {
            $this->db->table('cars')->where('id', $rental['car_id'])->update([
                'status' => 'available',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->session->set_flashdata('success', 'Rental cancelled.');
        redirect('/admin/rentals');
    }

    /**
     * Show all payments
     */
    public function payments()
    {
        $this->requireAdmin();

        $payments = $this->PaymentModel->getAllPayments();
        
        $data = [
            'payments' => $payments,
            'username' => $this->session->userdata('username')
        ];

        $this->call->view('admin/payments', $data);
    }

    /**
     * Manage car images (color variants)
     */
    public function carImages($carId)
    {
        $this->requireAdmin();

        $car = $this->CarModel->getById($carId);
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/admin/cars');
        }

        $images = [];
        try {
            $images = $this->CarImageModel->getByCarId($carId) ?: [];
        } catch (Throwable $e) {
            $images = [];
        }

        $data = [
            'car' => $car,
            'images' => $images,
            'username' => $this->session->userdata('username')
        ];

        $this->call->view('admin/car_images', $data);
    }

    /**
     * Handle upload or URL add for a car variant image
     */
    public function addCarImage()
    {
        $this->requireAdmin();

        $carId = (int)$this->io->post('car_id');
        $color = trim((string)$this->io->post('color'));
        $isPrimary = (int)($this->io->post('is_primary') ? 1 : 0);
        $imageUrlInput = trim((string)$this->io->post('image_url'));

        if (!$carId) { $this->session->set_flashdata('error', 'Invalid car.'); redirect('/admin/cars'); }

        $car = $this->CarModel->getById($carId);
        if (!$car) { $this->session->set_flashdata('error', 'Car not found.'); redirect('/admin/cars'); }

        $finalPath = '';
        // Handle upload if provided
        if (isset($_FILES['image']) && is_array($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $uploadsDirFs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cars';
            $this->call->helper('directory');
            if (!is_dir_usable($uploadsDirFs)) {
                $this->session->set_flashdata('error', 'Upload directory is not writable.');
                redirect('/admin/cars/images/' . $carId);
            }
            $this->call->library('Upload');
            $u = new Upload($_FILES['image']);
            $u->is_image();
            $u->allowed_extensions(['jpg','jpeg','png','gif']);
            $u->allowed_mimes(['image/jpg','image/jpeg','image/png','image/gif']);
            $u->max_size(5);
            $u->set_dir($uploadsDirFs);
            $u->encrypt_name();
            if ($u->do_upload()) {
                $saved = $u->get_filename();
                // store relative path for robustness; views auto-prefix to full URL
                $finalPath = 'public/uploads/cars/' . $saved;
            } else {
                $errors = $u->get_errors();
                $this->session->set_flashdata('error', 'Image upload failed: ' . implode('; ', (array)$errors));
                redirect('/admin/cars/images/' . $carId);
            }
        }

        // Fallback to URL if provided and no upload
        if ($finalPath === '' && $imageUrlInput !== '') {
            $finalPath = $imageUrlInput;
        }

        if ($finalPath === '') {
            $this->session->set_flashdata('error', 'Please upload an image or provide an image URL.');
            redirect('/admin/cars/images/' . $carId);
        }

        // Insert record
        $now = date('Y-m-d H:i:s');
        try {
            // If setting primary, clear previous primaries for this car
            if ($isPrimary) {
                $this->db->table('car_images')->where('car_id', $carId)->update(['is_primary' => 0, 'updated_at' => $now]);
            }
            $this->db->table('car_images')->insert([
                'car_id' => $carId,
                'color' => $color !== '' ? $color : null,
                'image_path' => $finalPath,
                'is_primary' => $isPrimary,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => null,
            ]);
            $this->session->set_flashdata('success', 'Image added.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Failed to add image: ' . $e->getMessage());
        }

        redirect('/admin/cars/images/' . $carId);
    }

    /** Delete a car variant image */
    public function deleteCarImage($imageId, $carId)
    {
        $this->requireAdmin();
        $imageId = (int)$imageId; $carId = (int)$carId;
        try {
            $this->db->table('car_images')->where('id', $imageId)->where('car_id', $carId)->delete();
            $this->session->set_flashdata('success', 'Image deleted.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Failed to delete image: ' . $e->getMessage());
        }
        redirect('/admin/cars/images/' . $carId);
    }

    /** Set an image as primary for a car */
    public function setPrimaryCarImage($imageId, $carId)
    {
        $this->requireAdmin();
        $imageId = (int)$imageId; $carId = (int)$carId;
        $now = date('Y-m-d H:i:s');
        try {
            $this->db->table('car_images')->where('car_id', $carId)->update(['is_primary' => 0, 'updated_at' => $now]);
            $this->db->table('car_images')->where('id', $imageId)->where('car_id', $carId)->update(['is_primary' => 1, 'updated_at' => $now]);
            $this->session->set_flashdata('success', 'Primary image updated.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Failed to set primary image: ' . $e->getMessage());
        }
        redirect('/admin/cars/images/' . $carId);
    }

    /**
     * Show all users
     */
    public function users()
    {
        $this->requireAdmin();

        $users = $this->UserModel->getAllUsers();
        
        $data = [
            'users' => $users,
            'username' => $this->session->userdata('username')
        ];

        $this->call->view('admin/users', $data);
    }

    /**
     * Reports page - show various business reports
     */
    public function reports()
    {
        $this->requireAdmin();

        // Get filter parameters
        $reportType = $this->io->get('type') ?? 'overview';
        $startDate = $this->io->get('start_date') ?? date('Y-m-01'); // First day of current month
        $endDate = $this->io->get('end_date') ?? date('Y-m-d'); // Today
        $period = $this->io->get('period') ?? 'monthly'; // daily, weekly, monthly, yearly

        // Get report data based on type
        $reportData = [];
        
        switch ($reportType) {
            case 'revenue':
                $reportData = $this->getRevenueReport($startDate, $endDate, $period);
                break;
            case 'rentals':
                $reportData = $this->getRentalReport($startDate, $endDate);
                break;
            case 'cars':
                $reportData = $this->getCarUtilizationReport($startDate, $endDate);
                break;
            case 'customers':
                $reportData = $this->getCustomerReport($startDate, $endDate);
                break;
            default:
                $reportData = $this->getOverviewReport($startDate, $endDate);
                break;
        }

        $data = [
            'username' => $this->session->userdata('username'),
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period' => $period,
            'report_data' => $reportData
        ];

        $this->call->view('admin/reports', $data);
    }

    /**
     * Export report as CSV
     */
    public function exportReport()
    {
        $this->requireAdmin();

        $reportType = $this->io->get('type') ?? 'overview';
        $startDate = $this->io->get('start_date') ?? date('Y-m-01');
        $endDate = $this->io->get('end_date') ?? date('Y-m-d');
        $period = $this->io->get('period') ?? 'monthly';

        // Get report data
        $reportData = [];
        switch ($reportType) {
            case 'revenue':
                $reportData = $this->getRevenueReport($startDate, $endDate, $period);
                break;
            case 'rentals':
                $reportData = $this->getRentalReport($startDate, $endDate);
                break;
            case 'cars':
                $reportData = $this->getCarUtilizationReport($startDate, $endDate);
                break;
            case 'customers':
                $reportData = $this->getCustomerReport($startDate, $endDate);
                break;
            default:
                $reportData = $this->getOverviewReport($startDate, $endDate);
                break;
        }

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . $reportType . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers based on report type
        if (!empty($reportData['data'])) {
            $headers = array_keys($reportData['data'][0]);
            fputcsv($output, $headers);
            
            foreach ($reportData['data'] as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Get overview report data
     */
    private function getOverviewReport($startDate, $endDate)
    {
        $stats = [];
        
        // Revenue stats
        $revenueQuery = $this->db->raw(
            "SELECT SUM(amount) as total_revenue 
             FROM payments 
             WHERE payment_status = 'completed' 
             AND payment_date >= ? 
             AND payment_date <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $revenueQuery['total_revenue'] ?? 0;

        // Rentals count
        $rentalsQuery = $this->db->raw(
            "SELECT COUNT(*) as cnt 
             FROM rentals 
             WHERE created_at >= ? 
             AND created_at <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);
        $stats['total_rentals'] = $rentalsQuery['cnt'] ?? 0;

        // Completed rentals
        $completedQuery = $this->db->raw(
            "SELECT COUNT(*) as cnt 
             FROM rentals 
             WHERE status = 'completed' 
             AND created_at >= ? 
             AND created_at <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);
        $stats['completed_rentals'] = $completedQuery['cnt'] ?? 0;

        // Average rental value
        $avgQuery = $this->db->raw(
            "SELECT AVG(total_amount) as avg_rental 
             FROM rentals 
             WHERE created_at >= ? 
             AND created_at <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);
        $stats['avg_rental_value'] = $avgQuery['avg_rental'] ?? 0;

        // Top cars by rentals
        $topCars = $this->db->raw(
            "SELECT c.make, c.model, c.plate_number, COUNT(*) as rental_count, SUM(r.total_amount) as total_revenue
             FROM rentals r
             JOIN cars c ON c.id = r.car_id
             WHERE r.created_at >= ?
             AND r.created_at <= ?
             GROUP BY r.car_id
             ORDER BY rental_count DESC
             LIMIT 10",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
        $stats['top_cars'] = $topCars;

        // Top customers
        $topCustomers = $this->db->raw(
            "SELECT u.first_name, u.last_name, u.email, COUNT(*) as rental_count, SUM(r.total_amount) as total_spent
             FROM rentals r
             JOIN users u ON u.id = r.user_id
             WHERE r.created_at >= ?
             AND r.created_at <= ?
             GROUP BY r.user_id
             ORDER BY total_spent DESC
             LIMIT 10",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
        $stats['top_customers'] = $topCustomers;

        return [
            'stats' => $stats,
            'data' => []
        ];
    }

    /**
     * Get revenue report data
     */
    private function getRevenueReport($startDate, $endDate, $period)
    {
        $stats = [];
        
        // Total revenue
        $totalQuery = $this->db->raw(
            "SELECT SUM(amount) as total 
             FROM payments 
             WHERE payment_status = 'completed' 
             AND payment_date >= ? 
             AND payment_date <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $totalQuery['total'] ?? 0;

        // Revenue by payment method
        $byMethod = $this->db->raw(
            "SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
             FROM payments 
             WHERE payment_status = 'completed' 
             AND payment_date >= ? 
             AND payment_date <= ?
             GROUP BY payment_method",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_method'] = $byMethod;

        // Revenue over time (based on period)
        $dateFormat = 'DATE(payment_date)';
        if ($period === 'weekly') {
            $dateFormat = "DATE_FORMAT(payment_date, '%Y-%u')";
        } elseif ($period === 'monthly') {
            $dateFormat = "DATE_FORMAT(payment_date, '%Y-%m')";
        } elseif ($period === 'yearly') {
            $dateFormat = "DATE_FORMAT(payment_date, '%Y')";
        }

        $overTime = $this->db->raw(
            "SELECT {$dateFormat} as period, SUM(amount) as revenue, COUNT(*) as transactions 
             FROM payments 
             WHERE payment_status = 'completed' 
             AND payment_date >= ? 
             AND payment_date <= ?
             GROUP BY period 
             ORDER BY period ASC",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
        $stats['over_time'] = $overTime;

        return [
            'stats' => $stats,
            'data' => $overTime
        ];
    }

    /**
     * Get rental report data
     */
    private function getRentalReport($startDate, $endDate)
    {
        $stats = [];
        
        // Get all rentals in period
        $rentals = $this->db->raw(
            "SELECT r.*, u.first_name, u.last_name, c.make, c.model, c.plate_number 
             FROM rentals r
             JOIN users u ON u.id = r.user_id
             JOIN cars c ON c.id = r.car_id
             WHERE r.created_at >= ? 
             AND r.created_at <= ?
             ORDER BY r.created_at DESC",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);

        // Status breakdown
        $statusBreakdown = [];
        foreach (['pending', 'confirmed', 'active', 'completed', 'cancelled'] as $status) {
            $countQuery = $this->db->raw(
                "SELECT COUNT(*) as cnt 
                 FROM rentals 
                 WHERE status = ? 
                 AND created_at >= ? 
                 AND created_at <= ?",
                [$status, $startDate, $endDate . ' 23:59:59']
            )->fetch(PDO::FETCH_ASSOC);
            $statusBreakdown[$status] = $countQuery['cnt'] ?? 0;
        }

        // Average rental duration
        $avgDuration = $this->db->raw(
            "SELECT AVG(total_days) as avg_days 
             FROM rentals 
             WHERE created_at >= ? 
             AND created_at <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);

        $stats['rentals'] = $rentals;
        $stats['total'] = count($rentals);
        $stats['status_breakdown'] = $statusBreakdown;
        $stats['avg_duration'] = $avgDuration['avg_days'] ?? 0;

        return [
            'stats' => $stats,
            'data' => $rentals
        ];
    }

    /**
     * Get car utilization report data
     */
    private function getCarUtilizationReport($startDate, $endDate)
    {
        $stats = [];
        
        // Get all cars with their rental statistics
        $cars = $this->db->raw(
            "SELECT c.*, 
                    COUNT(r.id) as rental_count,
                    SUM(CASE WHEN r.status = 'completed' THEN r.total_days ELSE 0 END) as total_days_rented,
                    SUM(CASE WHEN r.status = 'completed' THEN r.total_amount ELSE 0 END) as total_revenue
             FROM cars c
             LEFT JOIN rentals r ON c.id = r.car_id 
                AND r.created_at >= ? 
                AND r.created_at <= ?
             GROUP BY c.id
             ORDER BY rental_count DESC",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);

        // Calculate utilization rate (days rented / total days in period)
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $totalDaysInPeriod = $start->diff($end)->days + 1;

        foreach ($cars as &$car) {
            $daysRented = $car['total_days_rented'] ?? 0;
            $car['utilization_rate'] = $totalDaysInPeriod > 0 ? 
                round(($daysRented / $totalDaysInPeriod) * 100, 2) : 0;
        }

        $stats['cars'] = $cars;
        $stats['total_cars'] = count($cars);
        $stats['total_days_in_period'] = $totalDaysInPeriod;

        return [
            'stats' => $stats,
            'data' => $cars
        ];
    }

    /**
     * Get customer report data
     */
    private function getCustomerReport($startDate, $endDate)
    {
        $stats = [];
        
        // Get customer statistics
        $customers = $this->db->raw(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.phone,
                    COUNT(r.id) as total_rentals,
                    SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) as completed_rentals,
                    SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_rentals,
                    SUM(r.total_amount) as total_spent,
                    MAX(r.created_at) as last_rental_date
             FROM users u
             INNER JOIN rentals r ON u.id = r.user_id
             WHERE r.created_at >= ? 
             AND r.created_at <= ?
             AND u.role = 'user'
             GROUP BY u.id
             ORDER BY total_spent DESC",
            [$startDate, $endDate . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);

        // New customers in period
        $newCustomersQuery = $this->db->raw(
            "SELECT COUNT(*) as cnt 
             FROM users 
             WHERE role = 'user' 
             AND created_at >= ? 
             AND created_at <= ?",
            [$startDate, $endDate . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);

        $stats['customers'] = $customers;
        $stats['total_customers'] = count($customers);
        $stats['new_customers'] = $newCustomersQuery['cnt'] ?? 0;

        return [
            'stats' => $stats,
            'data' => $customers
        ];
    }

    /**
     * Require admin authentication
     */
    private function requireAdmin()
    {
        if (!$this->session->userdata('isLoggedIn') || $this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('/admin/login');
        }
    }
}
