<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\CustomerAuth;
use App\Core\Validator;
use App\Core\View;
use App\Models\Customer;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (CustomerAuth::check()) { $this->redirect(url('/customer')); return; }
        View::render('login', ['title' => 'Login | SAKIB ENTERPRISE'], 'auth');
    }

    public function login(): void
    {
        Csrf::checkOrFail();
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($login === '' || $password === '') {
            $this->flash('error', 'Please enter your phone/email and password.');
            $this->back(url('/login'));
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $customer = CustomerAuth::attempt($login, $password, $ip);
        if (!$customer) {
            $this->flash('error', 'Invalid credentials or account locked. Please try again or reset your password.');
            $this->back(url('/login'));
        }
        $intended = $_SESSION['intended_url'] ?? '/customer';
        unset($_SESSION['intended_url']);
        $this->redirect(url($intended));
    }

    public function showRegister(): void
    {
        if (CustomerAuth::check()) { $this->redirect(url('/customer')); return; }
        if (setting('customer_login_enabled', '1') !== '1') {
            $this->flash('error', 'Registration is currently disabled.');
            $this->redirect(url('/login'));
        }
        View::render('register', ['title' => 'Register | SAKIB ENTERPRISE'], 'auth');
    }

    public function register(): void
    {
        Csrf::checkOrFail();
        if (setting('customer_login_enabled', '1') !== '1') { $this->redirect(url('/login')); }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? '') ?: null,
            'password' => $_POST['password'] ?? '',
            'confirm' => $_POST['confirm_password'] ?? '',
        ];
        $errors = [];
        if ($data['name'] === '') $errors[] = 'Name is required.';
        if ($data['phone'] === '' || !Validator::phone($data['phone'])) $errors[] = 'Valid phone number is required.';
        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
        if (strlen($data['password']) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($data['password'] !== $data['confirm']) $errors[] = 'Passwords do not match.';
        if (Customer::findByPhone($data['phone'])) $errors[] = 'This phone number is already registered.';
        if ($data['email'] && Customer::findByEmail($data['email'])) $errors[] = 'This email is already registered.';

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            $this->old($data);
            $this->back(url('/register'));
        }
        $id = Customer::register($data['name'], $data['phone'], $data['email'], $data['password']);
        $customer = Customer::find($id);
        CustomerAuth::login($customer);
        $this->flash('success', 'Welcome to SAKIB ENTERPRISE! Your account has been created.');
        $this->redirect(url('/customer'));
    }

    public function logout(): void
    {
        Csrf::checkOrFail();
        CustomerAuth::logout();
        $this->flash('success', 'You have been logged out.');
        $this->redirect(url('/login'));
    }
}
