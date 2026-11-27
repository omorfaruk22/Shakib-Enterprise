<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Admin;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(url('admin/dashboard'));
        }
        View::render('login', ['title' => 'Admin Login | SAKIB ENTERPRISE'], 'admin');
    }

    public function login(): void
    {
        Csrf::checkOrFail();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (Auth::tooManyAttempts($email, $ip)) {
            $this->flash('error', 'Too many failed attempts. Please try again in 15 minutes.');
            $this->redirect(url('admin/login'));
        }
        $admin = Auth::attempt($email, $password, $ip);
        if ($admin) {
            Admin::logActivity((int)$admin['id'], 'login', 'admin', (int)$admin['id'], 'Admin logged in', $ip);
            $this->flash('success', 'Welcome back, ' . $admin['name']);
            $this->redirect(url('admin/dashboard'));
        }
        $this->flash('error', 'Invalid email or password.');
        $this->redirect(url('admin/login'));
    }

    public function logout(): void
    {
        Csrf::checkOrFail();
        $user = Auth::user();
        if ($user) {
            Admin::logActivity((int)$user['id'], 'logout', 'admin', (int)$user['id'], 'Admin logged out', $_SERVER['REMOTE_ADDR'] ?? null);
        }
        Auth::logout();
        $this->redirect(url('admin/login'));
    }

    public function profile(): void
    {
        $user = Auth::user();
        View::render('profile', ['title' => 'Profile | SAKIB ENTERPRISE', 'admin' => $user], 'admin');
    }

    public function updateProfile(): void
    {
        Csrf::checkOrFail();
        $user = Auth::user();
        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if ($name === '') {
            $this->flash('error', 'Name is required.');
            $this->back(url('admin/profile'));
        }
        if ($password !== '' && strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
            $this->back(url('admin/profile'));
        }
        Admin::updateProfile((int)$user['id'], $name, $password !== '' ? $password : null);
        Admin::logActivity((int)$user['id'], 'update_profile', 'admin', (int)$user['id'], 'Profile updated', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Profile updated successfully.');
        $this->redirect(url('admin/profile'));
    }
}
