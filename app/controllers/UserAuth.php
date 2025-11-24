<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserAuth extends Controller {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show user login form
     */
    public function login()
    {
        // if already logged in, send to dashboard
        if ($this->session->userdata('isUserLoggedIn')) {
            // Already logged in users go to the public landing page
            redirect('/');
        }
        $data = [
            'redirect_to' => $this->io->get('redirect_to')
        ];
        $this->call->view('/user/login', $data);
    }

    /**
     * Process user login form
     */
    public function loginProcess()
    {
        $email = trim($this->io->post('email'));
        $password = $this->io->post('password');

        if (empty($email) || empty($password)) {
            $this->session->set_flashdata('error', 'Email and password are required.');
            redirect('/user/login');
        }

        $user = $this->UserModel->verifyPassword($email, $password);

        if (!$user) {
            $this->session->set_flashdata('error', 'Invalid credentials.');
            redirect('/user/login');
        }

        // Block login if email not yet verified
        if (empty($user['is_verified'])) {
            // Attempt to send/resend verification link silently
            try {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 60*60*24);
                $this->UserModel->setVerificationToken((int)$user['id'], $token, $expires);
                $verify_url = site_url('/user/verify?token=' . rawurlencode($token) . '&email=' . rawurlencode($email));
                $this->call->library('Mailer');
                $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333">'
                      . '<h2 style="color:#06b2a9;">Confirm your email</h2>'
                      . '<p>Please confirm your email by clicking the button below:</p>'
                      . '<p><a href="' . $verify_url . '" style="background:#06b2a9;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block">Verify Email</a></p>'
                      . '<p>Or copy and paste this link into your browser:<br>' . htmlspecialchars($verify_url) . '</p>'
                      . '<p style="color:#777">This link will expire in 24 hours.</p>'
                      . '</div>';
                $this->mailer->send($email, 'Verify your CarRental account', $body);
            } catch (\Throwable $e) {}

            $this->session->set_flashdata('error', 'Please verify your email first. We just sent you a new verification link.');
            $this->session->set_flashdata('open_login_modal', 1);
            redirect('/');
        }

        // set session data
        $this->session->set_userdata([
            'user_id'     => (int)$user['id'],
            'first_name'  => $user['first_name'],
            'last_name'   => $user['last_name'],
            'email'       => $user['email'],
            'isUserLoggedIn' => true
        ]);

        // regenerate session id if available
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }

    // Redirect to intended page if provided and safe (internal path)
        $redirect_to = $this->io->post('redirect_to');
        // Avoid PHP 8-only str_starts_with; safely allow only internal paths starting with '/'
        if (is_string($redirect_to) && strlen($redirect_to) > 0 && substr($redirect_to, 0, 1) === '/') {
            redirect($redirect_to);
        } else {
            // Default: go to the public landing page after login
            redirect('/');
        }
    }

    /**
     * Show user registration form
     */
    public function register()
    {
        // prevent logged in user from re-registering
        if ($this->session->userdata('isUserLoggedIn')) {
            redirect('/user/dashboard');
        }

        $this->call->view('/user/register');
    }

    /**
     * Process user registration form
     */
    public function registerProcess()
    {
        // Detect AJAX intent for JSON response
        $isAjax = false;
        try {
            $xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
            $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
            $isAjax = ($xhr === 'xmlhttprequest') || (strpos($accept, 'application/json') !== false);
        } catch (\Throwable $t) {}

        // Start an output buffer if AJAX to prevent stray output from breaking JSON
        if ($isAjax) {
            try { if (ob_get_level() === 0) { ob_start(); } } catch (\Throwable $t) {}
        }

        try {

        $first_name = trim($this->io->post('first_name'));
        $last_name = trim($this->io->post('last_name'));
        $email = trim($this->io->post('email'));
        $phone = trim($this->io->post('phone'));
        $password = $this->io->post('password');
        $password_confirm = $this->io->post('password_confirm');
        $license_number = trim($this->io->post('license_number'));
        $address = trim($this->io->post('address'));

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            if ($isAjax) {
                return $this->jsonResponse(400, ['success' => false, 'message' => 'All required fields must be filled.']);
            }
            $this->session->set_flashdata('error', 'All required fields must be filled.');
            redirect('/user/register');
        }

        if (empty($password_confirm)) {
            if ($isAjax) {
                return $this->jsonResponse(400, ['success' => false, 'message' => 'Please confirm your password.']);
            }
            $this->session->set_flashdata('error', 'Please confirm your password.');
            redirect('/user/register');
        }

        if ($password !== $password_confirm) {
            if ($isAjax) {
                return $this->jsonResponse(400, ['success' => false, 'message' => 'Passwords do not match.']);
            }
            $this->session->set_flashdata('error', 'Passwords do not match.');
            redirect('/user/register');
        }

        // check if email already exists
    // Check for existing account regardless of is_active to avoid unique constraint errors
    $existing = $this->UserModel->getByEmailAny($email);
        if ($existing) {
            if ($isAjax) {
                return $this->jsonResponse(400, ['success' => false, 'message' => 'Email already registered.']);
            }
            $this->session->set_flashdata('error', 'Email already registered.');
            redirect('/user/register');
        }

        $data = [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => $password,
            'license_number' => $license_number,
            'address'       => $address,
            'is_active'     => 1
        ];

    $userId = $this->UserModel->createUser($data);

        if ($userId) {
            // Generate verification token and send email
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 60*60*24); // 24 hours
            $this->UserModel->setVerificationToken($userId, $token, $expires);

            // Build verify URL
            $verify_url = site_url('/user/verify?token=' . rawurlencode($token) . '&email=' . rawurlencode($email));
            // Send email using Mailer (PHPMailer preferred)
            $this->call->library('Mailer');
            $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333">'
                  . '<h2 style="color:#06b2a9;">Confirm your email</h2>'
                  . '<p>Hi ' . htmlspecialchars($first_name) . ',</p>'
                  . '<p>Thanks for registering. Please confirm your email address by clicking the button below:</p>'
                  . '<p><a href="' . $verify_url . '" style="background:#06b2a9;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block">Verify Email</a></p>'
                  . '<p>Or copy and paste this link into your browser:<br>' . htmlspecialchars($verify_url) . '</p>'
                  . '<p style="color:#777">This link will expire in 24 hours.</p>'
                  . '</div>';
            try { $this->mailer->send($email, 'Verify your CarRental account', $body); } catch (\Throwable $e) {}

            if ($isAjax) {
                return $this->jsonResponse(200, ['success' => true, 'message' => 'Account created. Please check your email to verify, then login.']);
            }
            $successMsg = 'Account created. Please check your email to verify, then login.';
            if (strtolower(config_item('ENVIRONMENT') ?? 'development') === 'development') {
                $successMsg .= ' <br><small class="text-muted">DEV: If email is disabled locally, verify via this link: <a href="' . $verify_url . '">Verify now</a></small>';
            }
            $this->session->set_flashdata('success', $successMsg);
            // Open login modal after return to page
            $this->session->set_flashdata('open_login_modal', 1);
            $return_to = $this->io->post('return_to');
            if (is_string($return_to) && strlen($return_to) > 0 && substr($return_to, 0, 1) === '/') {
                redirect($return_to);
            }
            redirect('/');
        } else {
            if ($isAjax) {
                return $this->jsonResponse(500, ['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            $this->session->set_flashdata('error', 'Registration failed. Please try again.');
            redirect('/user/register');
        }
        } catch (\Throwable $ex) {
            // Log server-side for diagnosis
            try {
                $logPath = APP_DIR . '..' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'error.log';
                @file_put_contents($logPath, '['.date('Y-m-d H:i:s').'] Register error: '.$ex->getMessage().' in '.$ex->getFile().':'.$ex->getLine()."\n", FILE_APPEND);
            } catch (\Throwable $ignore) {}
            $msg = 'Registration failed. Please try again.';
            $env = strtolower((string)(config_item('ENVIRONMENT') ?? 'development'));
            if ($isAjax) {
                if ($env === 'development') { $msg .= ' (dev: ' . $ex->getMessage() . ')'; }
                return $this->jsonResponse(500, ['success' => false, 'message' => $msg]);
            }
            if ($env === 'development') { $msg .= ' (dev: ' . $ex->getMessage() . ')'; }
            $this->session->set_flashdata('error', $msg);
            redirect('/user/register');
        }
    }

    /**
     * Emit a clean JSON response and terminate execution
     */
    private function jsonResponse($statusCode, $payload)
    {
        $debugRaw = '';
        try {
            // Capture and clear any active output buffers
            $buffers = [];
            while (ob_get_level() > 0) {
                $buffers[] = @ob_get_clean();
            }
            $debugRaw = trim(implode("\n", array_filter($buffers, function($s){ return (string)$s !== ''; })));
        } catch (\Throwable $t) {}
        // In development, include truncated raw output for debugging parse issues
        try {
            $env = strtolower((string) (config_item('ENVIRONMENT') ?? 'development'));
            if ($env === 'development' && $debugRaw !== '' && is_array($payload)) {
                $payload['_debug_raw'] = substr($debugRaw, 0, 4000);
            }
        } catch (\Throwable $t) {}
        if (!headers_sent()) {
            http_response_code((int)$statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload);
        exit;
    }

    /**
     * Verify email endpoint
     */
    public function verifyEmail()
    {
        $token = trim($this->io->get('token'));
        $email = trim($this->io->get('email'));
        if (empty($token) || empty($email)) {
            $this->session->set_flashdata('error', 'Invalid verification link.');
            redirect('/');
        }
        $user = $this->UserModel->findByEmailAndToken($email, $token);
        if (!$user) {
            $this->session->set_flashdata('error', 'Verification link is invalid or already used.');
            redirect('/');
        }
        // Check expiry
        if (!empty($user['verification_expires']) && strtotime($user['verification_expires']) < time()) {
            $this->session->set_flashdata('error', 'Verification link has expired. Please request a new one.');
            redirect('/');
        }
        $this->UserModel->markVerified((int)$user['id']);
        $this->session->set_flashdata('success', 'Email verified successfully. You can now login.');
        $this->session->set_flashdata('open_login_modal', 1);
        redirect('/');
    }

    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        $email = trim($this->io->post('email'));
        if (empty($email)) {
            $this->session->set_flashdata('error', 'Please provide your email to resend verification.');
            redirect('/');
        }
        $user = $this->UserModel->getByEmailAny($email);
        if (!$user) {
            $this->session->set_flashdata('error', 'No account found for that email.');
            redirect('/');
        }
        if (!empty($user['is_verified'])) {
            $this->session->set_flashdata('success', 'Email already verified. You can login.');
            $this->session->set_flashdata('open_login_modal', 1);
            redirect('/');
        }
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 60*60*24);
        $this->UserModel->setVerificationToken((int)$user['id'], $token, $expires);
        $verify_url = site_url('/user/verify?token=' . rawurlencode($token) . '&email=' . rawurlencode($email));
        $this->call->library('Mailer');
        try {
            $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333">'
                  . '<h2 style="color:#06b2a9;">Confirm your email</h2>'
                  . '<p>Please confirm your email by clicking the button below:</p>'
                  . '<p><a href="' . $verify_url . '" style="background:#06b2a9;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block">Verify Email</a></p>'
                  . '<p>Or copy and paste this link into your browser:<br>' . htmlspecialchars($verify_url) . '</p>'
                  . '<p style="color:#777">This link will expire in 24 hours.</p>'
                  . '</div>';
            $ok = $this->mailer->send($email, 'Verify your CarRental account', $body);
            $this->session->set_flashdata('success', $ok ? 'Verification email sent. Please check your inbox.' : 'Unable to send verification email right now.');
        } catch (\Throwable $e) {
            $this->session->set_flashdata('error', 'Unable to send verification email right now.');
        }
        $this->session->set_flashdata('open_login_modal', 1);
        redirect('/');
    }

    /**
     * User logout
     */
    public function logout()
    {
        $this->session->sess_destroy();
        // After logout, send user to public landing page
        redirect('/');
    }

    /**
     * Require user authentication
     */
    protected function requireUser()
    {
        if (!$this->session->userdata('isUserLoggedIn')) {
            $this->session->set_flashdata('error', 'Please login to access this page.');
            redirect('/user/login');
        }
    }
}
?>
