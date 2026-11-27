<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\View;
use App\Models\Destination;
use App\Models\Admin;

class DestinationController extends Controller
{
    public function index(): void
    {
        View::render('destinations/index', ['title' => 'Destinations | SAKIB ENTERPRISE', 'destinations' => Destination::all(), 'admin' => Auth::user()], 'admin');
    }

    public function create(): void
    {
        View::render('destinations/form', ['title' => 'Add Destination | SAKIB ENTERPRISE', 'destination' => null, 'admin' => Auth::user()], 'admin');
    }

    public function store(): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        if (trim($data['name'] ?? '') === '') {
            $this->flash('error', 'Name is required.');
            $this->old($data);
            $this->back(url('admin/destinations/create'));
        }
        try {
            $image = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ? Upload::handle($_FILES['image'], 'destinations')
                : ($data['image_url'] ?? null);
            $id = Destination::create([
                'name' => trim($data['name']),
                'slug' => Validator::slug($data['name']),
                'description' => trim($data['description'] ?? ''),
                'image' => $image,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'create_destination', 'destination', $id, 'Destination created', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Destination created.');
            $this->redirect(url('admin/destinations'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->old($data);
            $this->back(url('admin/destinations/create'));
        }
    }

    public function edit(string $id): void
    {
        $d = Destination::find((int)$id);
        if (!$d) { http_response_code(404); die('Destination not found.'); }
        View::render('destinations/form', ['title' => 'Edit Destination | SAKIB ENTERPRISE', 'destination' => $d, 'admin' => Auth::user()], 'admin');
    }

    public function update(string $id): void
    {
        Csrf::checkOrFail();
        $d = Destination::find((int)$id);
        if (!$d) { http_response_code(404); die('Destination not found.'); }
        $data = $_POST;
        try {
            $image = $d['image'];
            if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new = Upload::handle($_FILES['image'], 'destinations');
                Upload::delete($image);
                $image = $new;
            } elseif (!empty($data['image_url'])) {
                $image = trim($data['image_url']);
            }
            Destination::update((int)$id, [
                'name' => trim($data['name']),
                'slug' => Validator::slug($data['name']),
                'description' => trim($data['description'] ?? ''),
                'image' => $image,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'update_destination', 'destination', (int)$id, 'Destination updated', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Destination updated.');
            $this->redirect(url('admin/destinations'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url('admin/destinations/' . $id . '/edit'));
        }
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        $d = Destination::find((int)$id);
        if ($d) Upload::delete($d['image']);
        Destination::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_destination', 'destination', (int)$id, 'Destination deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Destination deleted.');
        $this->redirect(url('admin/destinations'));
    }
}
