<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AdminAuth extends Controller {

    public function __construct()
    {
        parent::__construct();

        // load model
        $this->call->model('AdminModel');
    }

    /**
     * Show login form (view at app/views/auth/login.php)
     */
    public function login()
    {
        // if already logged in, send to dashboard
        if ($this->session->userdata('isLoggedIn')) {
            redirect('/admin/dashboard');
        }

        $this->call->view('/auth/login');
    }

    /**
     * Process login form
     */
    public function loginProcess()
    {
        $username = trim($this->io->post('username'));
        $password = $this->io->post('password');

        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Username and password are required.');
            redirect('/admin/login');
        }

        $admin = $this->AdminModel->getByUsername($username);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $this->session->set_flashdata('error', 'Invalid credentials.');
            redirect('/admin/login');
        }

        // set session data
        $this->session->set_userdata([
            'admin_id'   => (int)$admin['id'],
            'username'   => $admin['username'],
            'role'       => $admin['role'],
            'isLoggedIn' => true
        ]);

        // regenerate session id if available
        if (function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }

        redirect('/admin/dashboard');
    }

    /**
     * Show registration form (view at app/views/auth/register.php)
     */
    public function register()
    {
        // prevent logged in user from re-registering
        if ($this->session->userdata('isLoggedIn')) {
            redirect('/admin/dashboard');
        }

        $this->call->view('/auth/register');
    }

    /**
     * Process registration form
     */
    public function registerProcess()
    {
        $username = trim($this->io->post('username'));
        $password = $this->io->post('password');
        $password_confirm = $this->io->post('password_confirm');

        if (empty($username) || empty($password)) {
            $this->session->set_flashdata('error', 'Username and password are required.');
            redirect('/admin/register');
        }

        if (empty($password_confirm)) {
            $this->session->set_flashdata('error', 'Please confirm your password.');
            redirect('/admin/register');
        }

        if ($password !== $password_confirm) {
            $this->session->set_flashdata('error', 'Passwords do not match.');
            redirect('/admin/register');
        }

        // check if username already exists
        $existing = $this->AdminModel->getByUsername($username);
        if ($existing) {
            $this->session->set_flashdata('error', 'Username already taken.');
            redirect('/admin/register');
        }

        $data = [
            'username'  => $username,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role'      => 'admin',
            'created_at'=> date('Y-m-d H:i:s')
        ];

        $this->AdminModel->createAdmin($data);

        $this->session->set_flashdata('success', 'Account created. Please login.');
        redirect('/admin/login');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('/admin/login');
    }

    /**
     * Small helper you can call in other controllers:
     * $this->requireAdmin();  -- to protect pages
     */
    protected function requireAdmin()
    {
        if (!$this->session->userdata('isLoggedIn') || $this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('/admin/login');
        }
    }
}
