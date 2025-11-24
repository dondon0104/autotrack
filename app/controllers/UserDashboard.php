<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserDashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function isAjax()
    {
        $xrw = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
        return ($xrw === 'xmlhttprequest') || ($this->io->post('ajax') == '1') || (strpos($accept, 'application/json') !== false);
    }

    private function jsonResponse($statusCode, $payload)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function computePaymentSummary($rental)
    {
        $rentalId = (int)($rental['id'] ?? 0);
        $total = (float) ($rental['total_amount'] ?? 0);
        $paid = (float) $this->PaymentModel->getPaidTotalByRental($rentalId);
        $allowPartial = (bool) config_item('allow_partial_payments');
        $depositRate = (float) config_item('deposit_rate');
        $minDepositAmount = (float) config_item('min_deposit_amount');
        $requiredDeposit = $allowPartial ? max($total * $depositRate, $minDepositAmount) : $total;
        $remaining = max(0, $total - $paid);
        $neededForDeposit = max(0, $requiredDeposit - $paid);

        return [
            'total' => $total,
            'paid' => $paid,
            'remaining' => $remaining,
            'allow_partial' => $allowPartial,
            'deposit_rate' => $depositRate,
            'required_deposit' => $requiredDeposit,
            'needed_for_deposit' => $neededForDeposit
        ];
    }

    /**
     * User dashboard - show available cars
     */
    public function index()
    {
        // Publicly viewable dashboard: no login required to browse cars
        $searchCriteria = [
            'available_only' => true
        ];

        // Handle search filters
        if ($this->io->get('make')) {
            $searchCriteria['make'] = $this->io->get('make');
        }
        if ($this->io->get('model')) {
            $searchCriteria['model'] = $this->io->get('model');
        }
        if ($this->io->get('year')) {
            $searchCriteria['year'] = $this->io->get('year');
        }
        if ($this->io->get('fuel_type')) {
            $searchCriteria['fuel_type'] = $this->io->get('fuel_type');
        }
        if ($this->io->get('transmission')) {
            $searchCriteria['transmission'] = $this->io->get('transmission');
        }
        if ($this->io->get('min_price')) {
            $searchCriteria['min_price'] = $this->io->get('min_price');
        }
        if ($this->io->get('max_price')) {
            $searchCriteria['max_price'] = $this->io->get('max_price');
        }
        if ($this->io->get('seating_capacity')) {
            $searchCriteria['seating_capacity'] = $this->io->get('seating_capacity');
        }

        // Ensure all expected keys exist for the view (prevents undefined key warnings)
        $criteriaDefaults = [
            'make' => '', 'model' => '', 'year' => '', 'fuel_type' => '',
            'transmission' => '', 'min_price' => '', 'max_price' => '', 'seating_capacity' => ''
        ];
        $searchCriteria = array_merge($criteriaDefaults, $searchCriteria);

        $cars = $this->CarModel->searchCars($searchCriteria);

        $isLoggedIn = $this->session->userdata('isUserLoggedIn') ? true : false;
        $userName = $isLoggedIn
            ? trim(($this->session->userdata('first_name') ?: '') . ' ' . ($this->session->userdata('last_name') ?: ''))
            : 'Guest';

        $data = [
            'cars' => $cars,
            'user_name' => $userName,
            'is_logged_in' => $isLoggedIn,
            'search_criteria' => $searchCriteria
        ];

        $this->call->view('user/dashboard', $data);
    }

    /**
     * View car details
     */
    public function viewCar($carId)
    {
        $this->requireUser();

        $car = $this->CarModel->getById($carId);
        
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/user/dashboard');
        }

        // Support variant colors/images similar to public details
        $selectedVariant = $this->io->get('variant');
        $variantColors = [];
        $variantImage = null;
        if (property_exists($this, 'CarImageModel') && $this->CarImageModel) {
            try {
                $variantColors = $this->CarImageModel->getColorsForCar($carId);
                if (!empty($selectedVariant)) {
                    $vi = $this->CarImageModel->getByCarIdAndColor($carId, $selectedVariant);
                    if ($vi && !empty($vi['image_path'])) {
                        $variantImage = $vi['image_path'];
                    }
                }
                if (!$variantImage) {
                    $primary = $this->CarImageModel->getPrimaryByCarId($carId);
                    if ($primary && !empty($primary['image_path'])) {
                        $variantImage = $primary['image_path'];
                    }
                }
            } catch (Throwable $e) { /* ignore */ }
        }

        $data = [
            'car' => $car,
            'user_name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'),
            'is_logged_in' => true,
            'selected_variant_color' => $selectedVariant,
            'variant_colors' => $variantColors,
            'variant_image_path' => $variantImage,
        ];

        $this->call->view('user/car_details', $data);
    }

    /**
     * Show rental form
     */
    public function rentCar($carId)
    {
        $this->requireUser();

        $car = $this->CarModel->getById($carId);
        
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/user/dashboard');
        }

        if ($car['status'] !== 'available') {
            $this->session->set_flashdata('error', 'This car is not available for rental.');
            redirect('/user/dashboard');
        }

        $data = [
            'car' => $car,
            'user_name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name')
        ];

        $this->call->view('user/rent_car', $data);
    }

    /**
     * Process car rental
     */
    public function processRental()
    {
        $this->requireUser();

    $carId = $this->io->post('car_id');
    $rentalStart = $this->io->post('rental_start');
    $rentalEnd = $this->io->post('rental_end');
        $pickupLocation = $this->io->post('pickup_location');
        $returnLocation = $this->io->post('return_location');
        $notes = $this->io->post('notes');

        if (empty($carId) || empty($rentalStart) || empty($rentalEnd) || empty($pickupLocation) || empty($returnLocation)) {
            if ($this->isAjax()) {
                $this->jsonResponse(400, ['ok' => false, 'message' => 'All required fields must be filled.']);
            }
            $this->session->set_flashdata('error', 'All required fields must be filled.');
            redirect('/user/rent/' . $carId);
        }

    // Validate dates (date-only). Normalize to day boundaries for storage and conflict checks.
    $startDate = new DateTime($rentalStart);
    $endDate = new DateTime($rentalEnd);
    $today = new DateTime('today');

        if ($startDate < $today) {
            if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>'Rental start date cannot be in the past.']); }
            $this->session->set_flashdata('error', 'Rental start date cannot be in the past.');
            redirect('/user/rent/' . $carId);
        }

        if ($endDate <= $startDate) {
            if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>'Rental end date must be after start date.']); }
            $this->session->set_flashdata('error', 'Rental end date must be after start date.');
            redirect('/user/rent/' . $carId);
        }

        // Build normalized datetime strings covering full days
        $startNorm = (new DateTime($startDate->format('Y-m-d') . ' 00:00:00'))->format('Y-m-d H:i:s');
        $endNorm = (new DateTime($endDate->format('Y-m-d') . ' 23:59:59'))->format('Y-m-d H:i:s');

        // Check if car is available for the period using normalized bounds
        $conflicts = $this->RentalModel->checkConflicts($carId, $startNorm, $endNorm);
        if (!empty($conflicts)) {
            if ($this->isAjax()) { $this->jsonResponse(409, ['ok'=>false,'message'=>'Car is not available for the selected period.']); }
            $this->session->set_flashdata('error', 'Car is not available for the selected period.');
            redirect('/user/rent/' . $carId);
        }

        $car = $this->CarModel->getById($carId);
        
        $rentalData = [
            'user_id' => $this->session->userdata('user_id'),
            'car_id' => $carId,
            'rental_start' => $startNorm,
            'rental_end' => $endNorm,
            'daily_rate' => $car['daily_rate'],
            'tax_rate' => 12.00, // 12% tax
            'status' => 'pending',
            'pickup_location' => $pickupLocation,
            'return_location' => $returnLocation,
            'notes' => $notes
        ];

        $rentalId = $this->RentalModel->createRental($rentalData);

        if ($rentalId) {
            // If AJAX, return payment summary for modal or redirect to contract if required
            if ($this->isAjax()) {
                $rental = $this->RentalModel->getById($rentalId);
                $summary = $this->computePaymentSummary($rental);
                $payload = [
                    'ok' => true,
                    'message' => 'Rental created successfully.',
                    'rental_id' => $rentalId,
                    'rental' => $rental,
                    'payment_summary' => $summary,
                    'payment_url' => site_url('/user/payment')
                ];
                if ((bool)config_item('require_contract_before_payment') && empty($rental['is_contract_signed'])) {
                    $payload['contract_needed'] = true;
                    $payload['contract_url'] = site_url('/user/contract/' . $rentalId);
                }
                $this->jsonResponse(200, $payload);
            }
            if ((bool)config_item('require_contract_before_payment')) {
                $this->session->set_flashdata('success', 'Rental created. Please sign the digital contract first.');
                redirect('/user/contract/' . $rentalId);
            } else {
                $this->session->set_flashdata('success', 'Rental request submitted successfully. Please proceed to payment.');
                redirect('/user/payment/' . $rentalId);
            }
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(500, [ 'ok' => false, 'message' => 'Failed to create rental request. Please try again.' ]);
            }
            $this->session->set_flashdata('error', 'Failed to create rental request. Please try again.');
            redirect('/user/rent/' . $carId);
        }
    }

    /**
     * Show payment form
     */
    public function payment($rentalId)
    {
        $this->requireUser();

        $rental = $this->RentalModel->getById($rentalId);
        
        if (!$rental) {
            $this->session->set_flashdata('error', 'Rental not found.');
            redirect('/user/dashboard');
        }

        if ($rental['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('/user/dashboard');
        }

        // Require signed contract before payment (if enabled)
        if ((bool)config_item('require_contract_before_payment') && empty($rental['is_contract_signed'])) {
            $this->session->set_flashdata('error', 'Please sign the digital contract before making a payment.');
            redirect('/user/contract/' . $rentalId);
        }

        // Compute payment summary
        $data = [
            'rental' => $rental,
            'user_name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'),
            'payment_summary' => $this->computePaymentSummary($rental)
        ];

        $this->call->view('user/payment', $data);
    }

    /**
     * Process payment
     */
    public function processPayment()
    {
        $this->requireUser();

        $rentalId = $this->io->post('rental_id');
        $paymentMethod = $this->io->post('payment_method');
        $amount = $this->io->post('amount');

        if (empty($rentalId) || empty($paymentMethod) || empty($amount)) {
            if ($this->isAjax()) { $this->jsonResponse(400, ['ok'=>false,'message'=>'All payment fields are required.']); }
            $this->session->set_flashdata('error', 'All payment fields are required.');
            redirect('/user/payment/' . $rentalId);
        }

        $rental = $this->RentalModel->getById($rentalId);
        
        if (!$rental) {
            if ($this->isAjax()) { $this->jsonResponse(404, ['ok'=>false,'message'=>'Rental not found.']); }
            $this->session->set_flashdata('error', 'Rental not found.');
            redirect('/user/dashboard');
        }

        if ($rental['user_id'] != $this->session->userdata('user_id')) {
            if ($this->isAjax()) { $this->jsonResponse(403, ['ok'=>false,'message'=>'Access denied.']); }
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('/user/dashboard');
        }

        // Allow payments while pending or confirmed (to complete remaining balance)
        if (!in_array($rental['status'], ['pending', 'confirmed'])) {
            if ($this->isAjax()) { $this->jsonResponse(400, ['ok'=>false,'message'=>'This rental can no longer be paid.']); }
            $this->session->set_flashdata('error', 'This rental can no longer be paid.');
            redirect('/user/dashboard');
        }

        // Enforce contract signed before payment
        if ((bool)config_item('require_contract_before_payment') && empty($rental['is_contract_signed'])) {
            $msg = 'Please sign the digital contract before making a payment.';
            if ($this->isAjax()) { $this->jsonResponse(403, ['ok'=>false,'message'=>$msg, 'contract_url'=>site_url('/user/contract/' . $rentalId)]); }
            $this->session->set_flashdata('error', $msg);
            redirect('/user/contract/' . $rentalId);
        }

        $amount = (float) $amount;
        if ($amount <= 0) {
            if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>'Payment amount must be greater than zero.']); }
            $this->session->set_flashdata('error', 'Payment amount must be greater than zero.');
            redirect('/user/payment/' . $rentalId);
        }

        $total = (float) $rental['total_amount'];
        $paidSoFar = (float) $this->PaymentModel->getPaidTotalByRental($rentalId);
        $remaining = max(0, $total - $paidSoFar);
        if ($amount > $remaining + 0.01) {
            if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>'Payment exceeds remaining balance.']); }
            $this->session->set_flashdata('error', 'Payment exceeds remaining balance.');
            redirect('/user/payment/' . $rentalId);
        }

        $allowPartial = (bool) config_item('allow_partial_payments');
        if ($rental['status'] === 'pending' && $allowPartial) {
            $requiredDeposit = max($total * (float) config_item('deposit_rate'), (float) config_item('min_deposit_amount'));
            // Fixed minimum per payment while pending (not decreasing): must pay at least the deposit in a single transaction
            if ($paidSoFar < $requiredDeposit - 0.01 && $amount < $requiredDeposit - 0.01) {
                $msg = 'Minimum payment is ₱' . number_format($requiredDeposit, 2) . ' (30% deposit). You can pay more, but not less.';
                if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>$msg]); }
                $this->session->set_flashdata('error', $msg);
                redirect('/user/payment/' . $rentalId);
            }
        }

        // Process payment
        // If GCash API integration is enabled, create a charge and return redirect URL
        $gcashMode = config_item('gcash_mode') ?: 'manual';
        if ($paymentMethod === 'gcash' && $gcashMode === 'api') {
            $gateway = new PaymentGateway();
            try {
                $charge = $gateway->createGCashCharge($rental, $amount);
                // Create a pending payment record linked by reference_id
                $this->PaymentModel->createPayment([
                    'rental_id' => $rentalId,
                    'user_id' => $rental['user_id'],
                    'amount' => $amount,
                    'payment_method' => 'gcash',
                    'payment_status' => 'pending',
                    'transaction_id' => $charge['reference_id'],
                    'notes' => 'Initiated via Xendit GCash'
                ]);
                if ($this->isAjax()) {
                    $this->jsonResponse(200, [ 'ok' => true, 'redirect_url' => $charge['checkout_url'] ]);
                } else {
                    header('Location: ' . $charge['checkout_url']);
                    exit;
                }
            } catch (Exception $e) {
                if ($this->isAjax()) { $this->jsonResponse(500, [ 'ok'=>false, 'message' => 'Failed to create GCash charge: ' . $e->getMessage() ]); }
                $this->session->set_flashdata('error', 'Failed to create GCash charge.');
                redirect('/user/payment/' . $rentalId);
            }
        }

        // Manual/other payments path
        $transactionId = null;
        if ($paymentMethod === 'gcash') {
            $transactionId = trim((string)$this->io->post('gcash_reference'));
            if ($transactionId === '') {
                $msg = 'GCash Reference No. is required.';
                if ($this->isAjax()) { $this->jsonResponse(400, ['ok'=>false,'message'=>$msg]); }
                $this->session->set_flashdata('error', $msg);
                redirect('/user/payment/' . $rentalId);
            }
        }

        $paymentId = $this->PaymentModel->processPayment($rentalId, $amount, $paymentMethod, $transactionId);

        if ($this->isAjax()) {
            if ($paymentId) {
                $updatedRental = $this->RentalModel->getById($rentalId);
                $summary = $this->computePaymentSummary($updatedRental);
                $balance = $summary['remaining'];
                $this->jsonResponse(200, [
                    'ok' => true,
                    'message' => $balance > 0.01
                        ? ('Payment received. Remaining balance: ₱' . number_format($balance, 2) . '.')
                        : 'Payment processed successfully! Your rental is fully paid.',
                    'rental' => $updatedRental,
                    'payment_summary' => $summary,
                    'next_url' => $balance > 0.01 ? site_url('/user/payment/' . $rentalId) : site_url('/user/my-rentals')
                ]);
            } else {
                $this->jsonResponse(500, [ 'ok' => false, 'message' => 'Payment processing failed. Please try again.' ]);
            }
        } else {
            if ($paymentId) {
                // Decide next page: if balance remains, stay on payment page
                $newPaid = (float) $this->PaymentModel->getPaidTotalByRental($rentalId);
                $balance = max(0, $total - $newPaid);
                if ($balance > 0.01) {
                    $this->session->set_flashdata('success', 'Payment received. Remaining balance: ₱' . number_format($balance, 2) . '.');
                    redirect('/user/payment/' . $rentalId);
                } else {
                    $this->session->set_flashdata('success', 'Payment processed successfully! Your rental is fully paid.');
                    redirect('/user/my-rentals');
                }
            } else {
                $this->session->set_flashdata('error', 'Payment processing failed. Please try again.');
                redirect('/user/payment/' . $rentalId);
            }
        }
    }

    /**
     * Show user's rental history
     */
    public function myRentals()
    {
        $this->requireUser();

        $userId = $this->session->userdata('user_id');
        $rentals = $this->RentalModel->getByUserId($userId);

        $data = [
            'rentals' => $rentals,
            'user_name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name')
        ];

        $this->call->view('user/my_rentals', $data);
    }

    /**
     * Display the digital contract for a rental, allow signing
     */
    public function contract($rentalId)
    {
        $this->requireUser();

        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental) { $this->session->set_flashdata('error','Rental not found.'); redirect('/user/my-rentals'); }
        if ($rental['user_id'] != $this->session->userdata('user_id')) { $this->session->set_flashdata('error','Access denied.'); redirect('/user/my-rentals'); }

        $data = [
            'rental' => $rental,
            'user_name' => $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name')
        ];
        $this->call->view('user/contract', $data);
    }

    /**
     * Accept signature and generate/store contract assets
     */
    public function signContract()
    {
        $this->requireUser();
        $rentalId = (int)$this->io->post('rental_id');
        $sigData = (string)$this->io->post('signature'); // dataURL (base64)

        if (!$rentalId || !$sigData) {
            if ($this->isAjax()) { $this->jsonResponse(400, ['ok'=>false,'message'=>'Missing rental_id or signature']); }
            $this->session->set_flashdata('error','Missing data.');
            redirect('/user/my-rentals');
        }

        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental) { if ($this->isAjax()) { $this->jsonResponse(404, ['ok'=>false,'message'=>'Rental not found']); } redirect('/user/my-rentals'); }
        if ($rental['user_id'] != $this->session->userdata('user_id')) { if ($this->isAjax()) { $this->jsonResponse(403, ['ok'=>false,'message'=>'Access denied']); } redirect('/user/my-rentals'); }

        // Decode signature
        if (strpos($sigData, 'data:image/png;base64,') === 0) {
            $sigData = substr($sigData, 22);
        } elseif (strpos($sigData, 'base64,') !== false) {
            $sigData = substr($sigData, strpos($sigData, 'base64,') + 7);
        }
        $bin = base64_decode($sigData);
        if ($bin === false) {
            if ($this->isAjax()) { $this->jsonResponse(422, ['ok'=>false,'message'=>'Invalid signature data']); }
            $this->session->set_flashdata('error','Invalid signature.');
            redirect('/user/contract/' . $rentalId);
        }

        // Ensure directories
        $sigDir = ROOT_DIR . 'public/uploads/contracts/signatures/';
        $pdfDir = ROOT_DIR . 'public/uploads/contracts/pdfs/';
        if (!is_dir($sigDir)) { @mkdir($sigDir, 0777, true); }
        if (!is_dir($pdfDir)) { @mkdir($pdfDir, 0777, true); }

        $sigName = 'rental_' . $rentalId . '_' . time() . '.png';
        $sigPath = $sigDir . $sigName;
        file_put_contents($sigPath, $bin);

        $sigRel = 'uploads/contracts/signatures/' . $sigName;

        // Update rental as signed
        $this->RentalModel->updateRental($rentalId, [
            'contract_signed_at' => date('Y-m-d H:i:s'),
            'contract_signature' => $sigRel,
            'is_contract_signed' => 1,
        ]);

        // Try to render a PDF if Dompdf is available
        $pdfRel = null;
        $dompdfClass = '\\Dompdf\\Dompdf';
        if (class_exists($dompdfClass)) {
            try {
                $html = $this->renderContractHtml($rentalId);
                $dompdf = new $dompdfClass([ 'isRemoteEnabled' => true ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4');
                $dompdf->render();
                $pdfName = 'rental_' . $rentalId . '.pdf';
                $pdfPath = $pdfDir . $pdfName;
                file_put_contents($pdfPath, $dompdf->output());
                $pdfRel = 'uploads/contracts/pdfs/' . $pdfName;
                $this->RentalModel->updateRental($rentalId, [ 'contract_pdf' => $pdfRel ]);
            } catch (Throwable $e) {
                // swallow; PDF optional
            }
        }

        // Email the contract PDF to the user (if we have their email)
        try {
            $email = trim((string)($rental['email'] ?? ''));
            if ($email !== '') {
                $this->call->library('Mailer');
                $subject = 'Your Rental Contract #' . (int)$rentalId;
                $link = site_url('/user/contract/pdf/' . $rentalId);
                $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333">'
                    . '<h2 style="color:#0d6efd;margin:0 0 12px">Rental Contract Signed</h2>'
                    . '<p>Hi ' . htmlspecialchars(($rental['first_name'] ?? '')) . ',</p>'
                    . '<p>Thanks for signing your rental contract for the ' . htmlspecialchars(($rental['make'] ?? '')) . ' ' . htmlspecialchars(($rental['model'] ?? '')) . ' (Plate ' . htmlspecialchars(($rental['plate_number'] ?? '')) . ').</p>'
                    . '<p>You can view or download your contract here:</p>'
                    . '<p><a href="' . $link . '" style="background:#0d6efd;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block">View Contract PDF</a></p>'
                    . '<p>If the button does not work, copy this link into your browser:<br>' . htmlspecialchars($link) . '</p>'
                    . '</div>';
                $attachments = null;
                if ($pdfRel) {
                    $pdfPath = ROOT_DIR . 'public/' . ltrim($pdfRel, '/');
                    if (is_file($pdfPath)) { $attachments = $pdfPath; }
                }
                $this->mailer->send($email, $subject, $body, null, null, $attachments);
            }
        } catch (Throwable $e) {}

        // After signing, compute payment summary and guide user to payment page
        $rental = $this->RentalModel->getById($rentalId);
        $summary = $this->computePaymentSummary($rental);
        $nextUrl = site_url('/user/payment/' . $rentalId);
        $resp = [
            'ok' => true,
            'message' => 'Contract signed successfully.',
            'contract_pdf' => $pdfRel ? site_url('/' . $pdfRel) : null,
            'payment_summary' => $summary,
            'next_url' => $nextUrl
        ];

        if ($this->isAjax()) {
            $this->jsonResponse(200, $resp);
        }

        $this->session->set_flashdata('success', 'Contract signed successfully. Please proceed to payment.');
        // Redirect user directly to payment page after signing
        redirect($nextUrl);
    }

    /**
     * Serve the contract PDF if available; fallback to HTML
     */
    public function contractPdf($rentalId)
    {
        $this->requireUser();
        $rental = $this->RentalModel->getById($rentalId);
        if (!$rental || $rental['user_id'] != $this->session->userdata('user_id')) { $this->session->set_flashdata('error','Access denied.'); redirect('/user/my-rentals'); }
        $pdfRel = $rental['contract_pdf'] ?? null;
        if ($pdfRel) {
            $pdfPath = ROOT_DIR . 'public/' . ltrim($pdfRel,'/');
            if (is_file($pdfPath)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="contract_' . (int)$rentalId . '.pdf"');
                readfile($pdfPath);
                exit;
            }
        }
        // If no saved PDF, try to generate on the fly when Dompdf is available
        $dompdfClass = '\\Dompdf\\Dompdf';
        if (class_exists($dompdfClass)) {
            $html = $this->renderContractHtml($rentalId);
            $dompdf = new $dompdfClass([ 'isRemoteEnabled' => true ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="contract_' . (int)$rentalId . '.pdf"');
            echo $dompdf->output();
            exit;
        }
        // Fallback render HTML if PDF engine not available
        echo $this->renderContractHtml($rentalId);
        exit;
    }

    private function renderContractHtml($rentalId)
    {
        $rental = $this->RentalModel->getById($rentalId);
        ob_start();
        $data = [ 'rental' => $rental ];
        // render view into buffer
        $this->call->view('user/contract_template', $data);
        $html = ob_get_clean();
        return $html ?: '<h3>Contract</h3><p>Unable to render contract.</p>';
    }
    /**
     * Require user authentication
     */
    private function requireUser()
    {
        if (!$this->session->userdata('isUserLoggedIn')) {
            // If this is an AJAX/API-style request, return JSON instead of redirecting HTML
            if ($this->isAjax()) {
                $this->jsonResponse(401, [
                    'ok' => false,
                    'message' => 'Please login to access this action.',
                    'login_url' => site_url('/user/login')
                ]);
            }
            $this->session->set_flashdata('error', 'Please login to access this page.');
            redirect('/user/login');
        }
    }
}
?>
