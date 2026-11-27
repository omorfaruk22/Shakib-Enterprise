<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\View;
use App\Models\TourPackage;
use App\Models\HolidayPackage;
use App\Models\Destination;
use App\Models\Admin;

class PackageController extends Controller
{
    private function model(string $type)
    {
        return $type === 'holiday' ? HolidayPackage::class : TourPackage::class;
    }

    private function basePath(string $type): string
    {
        return $type === 'holiday' ? 'holiday-packages' : 'tour-packages';
    }

    public function index(string $type): void
    {
        $model = $this->model($type);
        View::render('packages/index', [
            'title' => ($type === 'holiday' ? 'Holiday' : 'Tour') . ' Packages | SAKIB ENTERPRISE',
            'packages' => $model::all(),
            'type' => $type,
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function create(string $type): void
    {
        View::render('packages/form', [
            'title' => 'Add Package | SAKIB ENTERPRISE',
            'pkg' => null, 'type' => $type,
            'destinations' => Destination::all(),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function store(string $type): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        $errors = Validator::required($data, ['title']);
        if ($errors) {
            $this->flash('error', 'Title is required.');
            $this->old($data);
            $this->back(url('admin/' . $this->basePath($type) . '/create'));
        }
        try {
            $image = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ? Upload::handle($_FILES['image'], $type === 'holiday' ? 'holidays' : 'tours')
                : ($data['image_url'] ?? null);
            $model = $this->model($type);
            $id = $model::create([
                'destination_id' => (int)($data['destination_id'] ?? 0) ?: null,
                'title' => trim($data['title']),
                'slug' => Validator::slug($data['title']),
                'short_description' => trim($data['short_description'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'duration_days' => (int)($data['duration_days'] ?? 3),
                'duration_nights' => (int)($data['duration_nights'] ?? 2),
                'price' => (float)($data['price'] ?? 0),
                'currency' => 'BDT',
                'image' => $image,
                'itinerary' => trim($data['itinerary'] ?? ''),
                'inclusions' => trim($data['inclusions'] ?? ''),
                'exclusions' => trim($data['exclusions'] ?? ''),
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'create_package', $type . '_package', $id, 'Package created: ' . $data['title'], $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Package created successfully.');
            $this->redirect(url('admin/' . $this->basePath($type)));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->old($data);
            $this->back(url('admin/' . $this->basePath($type) . '/create'));
        }
    }

    public function edit(string $type, string $id): void
    {
        $model = $this->model($type);
        $pkg = $model::find((int)$id);
        if (!$pkg) { http_response_code(404); die('Package not found.'); }
        View::render('packages/form', [
            'title' => 'Edit Package | SAKIB ENTERPRISE',
            'pkg' => $pkg, 'type' => $type,
            'destinations' => Destination::all(),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function update(string $type, string $id): void
    {
        Csrf::checkOrFail();
        $model = $this->model($type);
        $pkg = $model::find((int)$id);
        if (!$pkg) { http_response_code(404); die('Package not found.'); }
        $data = $_POST;
        try {
            $image = $pkg['image'];
            if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new = Upload::handle($_FILES['image'], $type === 'holiday' ? 'holidays' : 'tours');
                Upload::delete($image);
                $image = $new;
            } elseif (!empty($data['image_url'])) {
                $image = trim($data['image_url']);
            }
            $model::update((int)$id, [
                'destination_id' => (int)($data['destination_id'] ?? 0) ?: null,
                'title' => trim($data['title']),
                'slug' => Validator::slug($data['title']),
                'short_description' => trim($data['short_description'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'duration_days' => (int)($data['duration_days'] ?? 3),
                'duration_nights' => (int)($data['duration_nights'] ?? 2),
                'price' => (float)($data['price'] ?? 0),
                'currency' => 'BDT',
                'image' => $image,
                'itinerary' => trim($data['itinerary'] ?? ''),
                'inclusions' => trim($data['inclusions'] ?? ''),
                'exclusions' => trim($data['exclusions'] ?? ''),
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'update_package', $type . '_package', (int)$id, 'Package updated', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Package updated successfully.');
            $this->redirect(url('admin/' . $this->basePath($type)));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url('admin/' . $this->basePath($type) . '/' . $id . '/edit'));
        }
    }

    public function destroy(string $type, string $id): void
    {
        Csrf::checkOrFail();
        $model = $this->model($type);
        $pkg = $model::find((int)$id);
        if ($pkg) Upload::delete($pkg['image']);
        $model::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_package', $type . '_package', (int)$id, 'Package deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Package deleted.');
        $this->redirect(url('admin/' . $this->basePath($type)));
    }
}
