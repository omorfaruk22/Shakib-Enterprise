<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\ShipRoute;
use App\Models\Admin;

class ShipRouteController extends Controller
{
    public function index(): void
    {
        View::render('ship-routes/index', ['title' => 'Ship Routes | SAKIB ENTERPRISE', 'routes' => ShipRoute::all(), 'admin' => Auth::user()], 'admin');
    }

    public function create(): void
    {
        View::render('ship-routes/form', ['title' => 'Add Ship Route | SAKIB ENTERPRISE', 'route' => null, 'admin' => Auth::user()], 'admin');
    }

    public function store(): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        if (trim($data['route_name'] ?? '') === '' || trim($data['from_location'] ?? '') === '' || trim($data['to_location'] ?? '') === '') {
            $this->flash('error', 'Route name, from and to locations are required.');
            $this->old($data);
            $this->back(url('admin/ship-routes/create'));
        }
        $id = ShipRoute::create([
            'route_name' => trim($data['route_name']),
            'from_location' => trim($data['from_location']),
            'to_location' => trim($data['to_location']),
            'duration' => trim($data['duration'] ?? ''),
            'schedule' => trim($data['schedule'] ?? ''),
            'price_note' => trim($data['price_note'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ]);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'create_ship_route', 'ship_route', $id, 'Ship route created', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Ship route created.');
        $this->redirect(url('admin/ship-routes'));
    }

    public function edit(string $id): void
    {
        $r = ShipRoute::find((int)$id);
        if (!$r) { http_response_code(404); die('Route not found.'); }
        View::render('ship-routes/form', ['title' => 'Edit Ship Route | SAKIB ENTERPRISE', 'route' => $r, 'admin' => Auth::user()], 'admin');
    }

    public function update(string $id): void
    {
        Csrf::checkOrFail();
        $data = $_POST;
        ShipRoute::update((int)$id, [
            'route_name' => trim($data['route_name']),
            'from_location' => trim($data['from_location']),
            'to_location' => trim($data['to_location']),
            'duration' => trim($data['duration'] ?? ''),
            'schedule' => trim($data['schedule'] ?? ''),
            'price_note' => trim($data['price_note'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ]);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'update_ship_route', 'ship_route', (int)$id, 'Ship route updated', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Ship route updated.');
        $this->redirect(url('admin/ship-routes'));
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        ShipRoute::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_ship_route', 'ship_route', (int)$id, 'Ship route deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Ship route deleted.');
        $this->redirect(url('admin/ship-routes'));
    }
}
