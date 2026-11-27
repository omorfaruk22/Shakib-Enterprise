<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Core\View;
use App\Models\Testimonial;
use App\Models\Admin;

class TestimonialController extends Controller
{
    public function index(): void
    {
        View::render('testimonials/index', ['title' => 'Testimonials | SAKIB ENTERPRISE', 'testimonials' => Testimonial::all(), 'admin' => Auth::user()], 'admin');
    }

    public function create(): void
    {
        View::render('testimonials/form', ['title' => 'Add Testimonial | SAKIB ENTERPRISE', 'testimonial' => null, 'admin' => Auth::user()], 'admin');
    }

    public function store(): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        if (trim($data['customer_name'] ?? '') === '' || trim($data['message'] ?? '') === '') {
            $this->flash('error', 'Name and message are required.');
            $this->old($data);
            $this->back(url('admin/testimonials/create'));
        }
        try {
            $image = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ? Upload::handle($_FILES['image'], 'testimonials')
                : null;
            $id = Testimonial::create([
                'customer_name' => trim($data['customer_name']),
                'location' => trim($data['location'] ?? ''),
                'rating' => max(1, min(5, (int)($data['rating'] ?? 5))),
                'message' => trim($data['message']),
                'image' => $image,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'create_testimonial', 'testimonial', $id, 'Testimonial created', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Testimonial created.');
            $this->redirect(url('admin/testimonials'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url('admin/testimonials/create'));
        }
    }

    public function edit(string $id): void
    {
        $t = Testimonial::find((int)$id);
        if (!$t) { http_response_code(404); die('Testimonial not found.'); }
        View::render('testimonials/form', ['title' => 'Edit Testimonial | SAKIB ENTERPRISE', 'testimonial' => $t, 'admin' => Auth::user()], 'admin');
    }

    public function update(string $id): void
    {
        Csrf::checkOrFail();
        $t = Testimonial::find((int)$id);
        if (!$t) { http_response_code(404); die('Testimonial not found.'); }
        $data = $_POST;
        try {
            $image = $t['image'];
            if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new = Upload::handle($_FILES['image'], 'testimonials');
                Upload::delete($image);
                $image = $new;
            }
            Testimonial::update((int)$id, [
                'customer_name' => trim($data['customer_name']),
                'location' => trim($data['location'] ?? ''),
                'rating' => max(1, min(5, (int)($data['rating'] ?? 5))),
                'message' => trim($data['message']),
                'image' => $image,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'sort_order' => (int)($data['sort_order'] ?? 0),
            ]);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'update_testimonial', 'testimonial', (int)$id, 'Testimonial updated', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', 'Testimonial updated.');
            $this->redirect(url('admin/testimonials'));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url('admin/testimonials/' . $id . '/edit'));
        }
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        $t = Testimonial::find((int)$id);
        if ($t) Upload::delete($t['image']);
        Testimonial::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_testimonial', 'testimonial', (int)$id, 'Testimonial deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Testimonial deleted.');
        $this->redirect(url('admin/testimonials'));
    }
}
