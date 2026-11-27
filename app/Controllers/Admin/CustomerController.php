<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\View;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = Customer::count($search);
        $customers = Customer::all($search, $perPage, ($page - 1) * $perPage);
        View::render('customers/index', [
            'title' => 'Customers | SAKIB ENTERPRISE',
            'customers' => $customers, 'search' => $search,
            'page' => $page, 'pages' => (int)ceil($total / $perPage), 'total' => $total,
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function show(string $id): void
    {
        $customer = Customer::find((int)$id);
        if (!$customer) {
            http_response_code(404);
            die('Customer not found.');
        }
        View::render('customers/show', [
            'title' => 'Customer | SAKIB ENTERPRISE',
            'customer' => $customer,
            'bookings' => Customer::bookings((int)$id),
            'admin' => Auth::user(),
        ], 'admin');
    }
}
