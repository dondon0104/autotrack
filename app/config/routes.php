<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/


// Home page - public landing page
$router->get('/', 'Home::index');

// Test route to verify router is working
$router->get('/test', function() {
    echo "Router is working! If you see this, the router can match routes.";
    exit;
});

// Admin Authentication Routes
$router->get('/admin/login', 'AdminAuth::login');
$router->post('/admin/loginProcess', 'AdminAuth::loginProcess');
$router->get('/admin/register', 'AdminAuth::register');
$router->post('/admin/registerProcess', 'AdminAuth::registerProcess');
$router->get('/admin/logout', 'AdminAuth::logout');

// Admin Dashboard Routes
$router->get('/admin/dashboard', 'AdminDashboard::index');
$router->get('/admin/cars', 'AdminDashboard::cars');
$router->get('/admin/cars/add', 'AdminDashboard::addCar');
$router->post('/admin/cars/add', 'AdminDashboard::processAddCar');
$router->get('/admin/cars/edit/{id}', 'AdminDashboard::editCar');
$router->post('/admin/cars/edit', 'AdminDashboard::processEditCar');
$router->get('/admin/cars/delete/{id}', 'AdminDashboard::deleteCar');
$router->get('/admin/cars/images/{id}', 'AdminDashboard::carImages');
$router->post('/admin/cars/images/add', 'AdminDashboard::addCarImage');
$router->get('/admin/cars/images/delete/{imageId}/{carId}', 'AdminDashboard::deleteCarImage');
$router->get('/admin/cars/images/primary/{imageId}/{carId}', 'AdminDashboard::setPrimaryCarImage');
$router->get('/admin/rentals', 'AdminDashboard::rentals');
$router->get('/admin/rentals/view/{id}', 'AdminDashboard::viewRental');
$router->get('/admin/rentals/confirm/{id}', 'AdminDashboard::confirmRental');
$router->get('/admin/rentals/cancel/{id}', 'AdminDashboard::cancelRental');
$router->get('/admin/payments', 'AdminDashboard::payments');
$router->get('/admin/users', 'AdminDashboard::users');
$router->get('/admin/reports', 'AdminDashboard::reports');
$router->get('/admin/reports/export', 'AdminDashboard::exportReport');

// User Authentication Routes
$router->get('/user/login', 'UserAuth::login');
$router->post('/user/loginProcess', 'UserAuth::loginProcess');
$router->get('/user/register', 'UserAuth::register');
$router->post('/user/registerProcess', 'UserAuth::registerProcess');
$router->get('/user/verify', 'UserAuth::verifyEmail');
$router->post('/user/resend-verification', 'UserAuth::resendVerification');
$router->get('/user/logout', 'UserAuth::logout');

// User Dashboard Routes
$router->get('/user/dashboard', 'UserDashboard::index');
$router->get('/user/car/{id}', 'UserDashboard::viewCar');
$router->get('/user/rent/{id}', 'UserDashboard::rentCar');
$router->post('/user/rent', 'UserDashboard::processRental');
$router->get('/user/payment/{id}', 'UserDashboard::payment');
$router->post('/user/payment', 'UserDashboard::processPayment');
// Digital Contract routes
$router->get('/user/contract/{id}', 'UserDashboard::contract');
$router->post('/user/contract/sign', 'UserDashboard::signContract');
$router->get('/user/contract/pdf/{id}', 'UserDashboard::contractPdf');
// Webhook for payment provider callbacks
$router->post('/webhook/xendit', 'Webhook::xendit');
$router->post('/webhook/paymongo', 'Webhook::paymongo');
$router->get('/user/my-rentals', 'UserDashboard::myRentals');

// Public Customer Catalog (no login required)
$router->get('/customer', 'Customer::index');
$router->get('/customer/car/{id}', 'Customer::car');

// Public API: get variant image for a car by color (use URI segment, not query string)
$router->get('/api/cars/{id}/variant/{color}', 'Customer::variantImage');

// Development-only: run pending migrations quickly
if (strtolower(config_item('ENVIRONMENT') ?? 'production') === 'development') {
    $router->get('/dev/migrate', function() {
        $lava = lava_instance();
        $lava->call->library('Migration');
        try {
            $lava->migration->migrate();
            echo 'Migrations applied successfully.';
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Migration failed: ' . htmlspecialchars($e->getMessage());
        }
        exit;
    });
}
