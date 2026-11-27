<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Admin;

class ManagerController extends Controller
{
    public function index(): void
    {
        $pdo = \App\Core\Database::getConnection();
        $admins = $pdo->query("SELECT * FROM admins ORDER BY FIELD(role,'super_admin','admin','manager'), created_at ASC")->fetchAll();
        View::render('managers/index', ['title' => 'Admin Users | SAKIB ENTERPRISE', 'admins' => $admins, 'admin' => Auth::user()], 'admin');
    }

    public function create(): void
    {
        View::render('managers/form', ['title' => 'Add Admin / Manager', 'user' => null, 'admin' => Auth::user()], 'admin');
    }

    public function store(): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $role = in_array($data['role'] ?? 'admin', ['admin','manager','super_admin']) ? $data['role'] : 'manager';

        if ($name === '' || $email === '' || $password === '') {
            $this->flash('error', 'Name, email and password are required.');
            $this->back(url('/admin/managers/create'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address. Use a valid email (e.g. you@gmail.com).');
            $this->back(url('/admin/managers/create'));
        }
        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
            $this->back(url('/admin/managers/create'));
        }
        if (Admin::findByEmail($email)) {
            $this->flash('error', 'An account with this email already exists.');
            $this->back(url('/admin/managers/create'));
        }

        $id = Admin::create($name, $email, $password);
        \App\Core\Database::getConnection()->prepare("UPDATE admins SET role = ? WHERE id = ?")->execute([$role, $id]);
        $current = Auth::user();
        Admin::logActivity((int)$current['id'], 'create_admin', 'admin', $id, "Created $role: $email", $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', '✅ অ্যাকাউন্ট তৈরি হয়েছে! ' . htmlspecialchars($email) . ' ইমেইল দিয়ে লগইন করতে পারবেন।');
        $this->redirect(url('/admin/managers'));
    }

    public function edit(string $id): void
    {
        $stmt = \App\Core\Database::getConnection()->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([(int)$id]);
        $user = $stmt->fetch();
        if (!$user) { http_response_code(404); die('User not found.'); }
        View::render('managers/form', ['title' => 'Edit Admin User', 'user' => $user, 'admin' => Auth::user()], 'admin');
    }

    public function update(string $id): void
    {
        Csrf::checkOrFail();
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([(int)$id]);
        $target = $stmt->fetch();
        if (!$target) { http_response_code(404); die('User not found.'); }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $role = in_array($_POST['role'] ?? 'admin', ['admin','manager','super_admin']) ? $_POST['role'] : 'manager';
        $newPassword = $_POST['password'] ?? '';

        if ($name === '' || $email === '') {
            $this->flash('error', 'Name and email are required.');
            $this->back(url('/admin/managers/'.$id.'/edit'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address.');
            $this->back(url('/admin/managers/'.$id.'/edit'));
        }
        // Check duplicate email
        $dup = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $dup->execute([$email, (int)$id]);
        if ($dup->fetchColumn()) {
            $this->flash('error', 'Another account already uses this email.');
            $this->back(url('/admin/managers/'.$id.'/edit'));
        }

        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                $this->flash('error', 'Password must be at least 8 characters.');
                $this->back(url('/admin/managers/'.$id.'/edit'));
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET name=?, email=?, role=?, password_hash=? WHERE id=?")->execute([$name, $email, $role, $hash, (int)$id]);
        } else {
            $pdo->prepare("UPDATE admins SET name=?, email=?, role=? WHERE id=?")->execute([$name, $email, $role, (int)$id]);
        }

        $current = Auth::user();
        Admin::logActivity((int)$current['id'], 'update_admin', 'admin', (int)$id, "Updated user: $email ($role)", $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', '✅ ইউজার আপডেট হয়েছে!');
        $this->redirect(url('/admin/managers'));
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        $current = Auth::user();
        if ((int)$current['id'] === (int)$id) {
            $this->flash('error', 'আপনি নিজের অ্যাকাউন্ট ডিলিট করতে পারবেন না।');
            $this->back(url('/admin/managers'));
        }
        $pdo = \App\Core\Database::getConnection();
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([(int)$id]);
        Admin::logActivity((int)$current['id'], 'delete_admin', 'admin', (int)$id, 'Deleted admin user', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'User deleted.');
        $this->redirect(url('/admin/managers'));
    }
}
