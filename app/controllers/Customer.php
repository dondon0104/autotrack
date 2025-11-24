<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Customer extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // CarModel is autoloaded via app/config/autoload.php
    }

    /**
     * Public catalog of available cars (no login required)
     */
    public function index()
    {
        $criteria = ['available_only' => true];
        $promo = $this->io->get('promo');

        // Optional search filters
        if ($this->io->get('make')) $criteria['make'] = $this->io->get('make');
        if ($this->io->get('model')) $criteria['model'] = $this->io->get('model');
        if ($this->io->get('year')) $criteria['year'] = $this->io->get('year');
        if ($this->io->get('fuel_type')) $criteria['fuel_type'] = $this->io->get('fuel_type');
        if ($this->io->get('transmission')) $criteria['transmission'] = $this->io->get('transmission');
        if ($this->io->get('min_price')) $criteria['min_price'] = $this->io->get('min_price');
        if ($this->io->get('max_price')) $criteria['max_price'] = $this->io->get('max_price');
        if ($this->io->get('seating_capacity')) $criteria['seating_capacity'] = $this->io->get('seating_capacity');

        // Ensure all expected keys exist for view
        $criteria = array_merge([
            'make' => '', 'model' => '', 'year' => '', 'fuel_type' => '',
            'transmission' => '', 'min_price' => '', 'max_price' => '', 'seating_capacity' => ''
        ], $criteria);

        $cars = $this->CarModel->searchCars($criteria);

        $isLoggedIn = $this->session->userdata('isUserLoggedIn') ? true : false;
        $userName = $isLoggedIn
            ? trim(($this->session->userdata('first_name') ?: '') . ' ' . ($this->session->userdata('last_name') ?: ''))
            : 'Guest';

        $data = [
            'cars' => $cars,
            'is_logged_in' => $isLoggedIn,
            'user_name' => $userName,
            'search_criteria' => $criteria,
            'promo_code' => $promo,
        ];

        $this->call->view('customer/catalog', $data);
    }

    /**
     * Public car details page (no login required)
     */
    public function car($id)
    {
        $car = $this->CarModel->getById($id);
        if (!$car) {
            $this->session->set_flashdata('error', 'Car not found.');
            redirect('/customer');
        }

        // Optional selected color variant from query
        $selectedVariant = $this->io->get('variant');

        // Determine variant colors & select image per variant if available
        $variantColors = [];
        $variantImage = null;
        if (property_exists($this, 'CarImageModel') && $this->CarImageModel) {
            try {
                $variantColors = $this->CarImageModel->getColorsForCar($id);
                if (!empty($selectedVariant)) {
                    $vi = $this->CarImageModel->getByCarIdAndColor($id, $selectedVariant);
                    if ($vi && !empty($vi['image_path'])) {
                        $variantImage = $vi['image_path'];
                    }
                }
                // Fallback to primary car image if no color-specified image found
                if (!$variantImage) {
                    $primary = $this->CarImageModel->getPrimaryByCarId($id);
                    if ($primary && !empty($primary['image_path'])) {
                        $variantImage = $primary['image_path'];
                    }
                }
            } catch (Throwable $e) {
                // If car_images table doesn't exist or any error occurs, just ignore and use base image
            }
        }

        $isLoggedIn = $this->session->userdata('isUserLoggedIn') ? true : false;
        $userName = $isLoggedIn
            ? trim(($this->session->userdata('first_name') ?: '') . ' ' . ($this->session->userdata('last_name') ?: ''))
            : 'Guest';

        $data = [
            'car' => $car,
            'is_logged_in' => $isLoggedIn,
            'user_name' => $userName,
            'selected_variant_color' => $selectedVariant,
            'variant_colors' => $variantColors,
            'variant_image_path' => $variantImage,
        ];

        $this->call->view('customer/car_details', $data);
    }

    /**
     * API: Return variant image URL for a given car and color
     * GET /api/cars/{id}/variant?color=Red
     */
    public function variantImage($id, $color = '')
    {
        // Force JSON
        header('Content-Type: application/json');

        $car = $this->CarModel->getById($id);
        if (!$car) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Car not found']);
            return;
        }

        // Accept color from path segment; decode and trim
        $color = urldecode((string)$color);
        $color = trim($color);
        $image = null;
        if (!empty($color) && property_exists($this, 'CarImageModel') && $this->CarImageModel) {
            try {
                // First try exact match
                $vi = $this->CarImageModel->getByCarIdAndColor($id, $color);
                if ($vi && !empty($vi['image_path'])) {
                    $image = $vi['image_path'];
                } else {
                    // Fallback: case-insensitive match by fetching all and comparing
                    $all = $this->CarImageModel->getByCarId($id) ?: [];
                    foreach ($all as $row) {
                        if (isset($row['color']) && strcasecmp(trim($row['color']), $color) === 0 && !empty($row['image_path'])) {
                            $image = $row['image_path'];
                            break;
                        }
                    }
                }
            } catch (Throwable $e) {}
        }
        if (!$image && property_exists($this, 'CarImageModel') && $this->CarImageModel) {
            try {
                $primary = $this->CarImageModel->getPrimaryByCarId($id);
                if ($primary && !empty($primary['image_path'])) {
                    $image = $primary['image_path'];
                }
            } catch (Throwable $e) {}
        }
        if (!$image) {
            $image = $car['image_path'] ?? '';
        }

        // Normalize to absolute URL if it's a relative path
        $url = '';
        if (!empty($image)) {
            $raw = trim($image);
            if (preg_match('/^https?:\\/\\//i', $raw) || strpos($raw, 'data:') === 0) {
                $url = $raw;
            } else {
                $url = site_url('/' . ltrim($raw, '/'));
            }
        }

        echo json_encode([
            'ok' => true,
            'color' => $color,
            'image_url' => $url,
        ]);
    }
}
?>
