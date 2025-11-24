<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Home extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Featured cars (top 6 available)
        $featuredCars = [];
        try {
            if (property_exists($this, 'CarModel') && $this->CarModel) {
                $featuredCars = $this->CarModel->getAvailableCars(6);
            }
        } catch (Exception $e) {
            // Silently fail for homepage; log if logger available
            if (method_exists($this, 'logger')) {
                $this->logger->error('Failed to load featured cars: ' . $e->getMessage());
            }
            $featuredCars = [];
        }

        // Session state for dynamic navbar/CTA
        $isLoggedIn = $this->session->userdata('isUserLoggedIn') ? true : false;
        $userName = $isLoggedIn
            ? trim(($this->session->userdata('first_name') ?: '') . ' ' . ($this->session->userdata('last_name') ?: ''))
            : 'Guest';

        $data = [
            'featured_cars' => $featuredCars,
            'is_logged_in' => $isLoggedIn,
            'user_name' => $userName,
        ];

        $this->call->view('home/landing', $data);
    }
}
?>
