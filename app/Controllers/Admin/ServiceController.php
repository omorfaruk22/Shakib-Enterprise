<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\View;
use App\Models\Service;
use App\Models\Admin;

class ServiceController extends Controller
{
    public function index(): void
    {
        View::render('services/index', [
            'title' => 'Services | SAKIB ENTERPRISE',
            'services' => Service::all(),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function create(): void
    {
        View::render('services/form', ['title' => 'Add Service | SAKIB ENTERPRISE', 'service' => null, 'admin' => Auth::user()], 'admin');
    }

    public function store(): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        $errors = Validator::required($data, ['name']);
        if ($errors) {
            $this->flash('error', 'Name is required.');
            $this->old($data);
            $this->back(url('admin/services/create'));
        }
        try {
            $image = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ? Upload::handle($_FILES['image'], 'services')
                : ($data['image_url'] ?? null);
            $id = Service::create([
                'slug' => Validator::slug($data['name']),
                'name' => trim($data['name']),
                'short_description' => trim($data['short_description'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'icon' => trim($data['icon'] ?? 'fa-solid fa-plane'),
                'image' => $image,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'create_service', 'service', $id, 'Service created: ' . $data['name'], $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Service created successfully.');
            $this->redirect(url('admin/services'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->old($data);
            $this->back(url('admin/services/create'));
        }
    }

    public function edit(string $id): void
    {
        $service = Service::find((int)$id);
        if (!$service) { http_response_code(404); die('Service not found.'); }
        View::render('services/form', ['title' => 'Edit Service | SAKIB ENTERPRISE', 'service' => $service, 'admin' => Auth::user()], 'admin');
    }

    public function update(string $id): void
    {
        Csrf::checkOrFail();
        $service = Service::find((int)$id);
        if (!$service) { http_response_code(404); die('Service not found.'); }
        $data = $_POST;
        try {
            $image = $service['image'];
            if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new = Upload::handle($_FILES['image'], 'services');
                Upload::delete($image);
                $image = $new;
            } elseif (!empty($data['image_url'])) {
                $image = trim($data['image_url']);
            }
            Service::update((int)$id, [
                'slug' => Validator::slug($data['name']),
                'name' => trim($data['name']),
                'short_description' => trim($data['short_description'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'icon' => trim($data['icon'] ?? 'fa-solid fa-plane'),
                'image' => $image,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'update_service', 'service', (int)$id, 'Service updated', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Service updated successfully.');
            $this->redirect(url('admin/services'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url('admin/services/' . $id . '/edit'));
        }
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        $service = Service::find((int)$id);
        if ($service) Upload::delete($service['image']);
        Service::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_service', 'service', (int)$id, 'Service deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Service deleted.');
        $this->redirect(url('admin/services'));
    }
}
