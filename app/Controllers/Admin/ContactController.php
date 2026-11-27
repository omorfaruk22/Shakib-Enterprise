<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\ContactMessage;
use App\Models\Admin;

class ContactController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = ContactMessage::count($search);
        $messages = ContactMessage::all($search, $perPage, ($page - 1) * $perPage);
        View::render('contacts/index', [
            'title' => 'Contact Messages | SAKIB ENTERPRISE',
            'messages' => $messages, 'search' => $search,
            'page' => $page, 'pages' => (int)ceil($total / $perPage), 'total' => $total,
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function show(string $id): void
    {
        $msg = ContactMessage::find((int)$id);
        if (!$msg) { http_response_code(404); die('Message not found.'); }
        ContactMessage::markRead((int)$id, 1);
        View::render('contacts/show', ['title' => 'Message | SAKIB ENTERPRISE', 'message' => $msg, 'admin' => Auth::user()], 'admin');
    }

    public function markRead(string $id): void
    {
        Csrf::checkOrFail();
        ContactMessage::markRead((int)$id, isset($_POST['is_read']) ? 1 : 0);
        $this->flash('success', 'Message status updated.');
        $this->back(url('admin/contacts'));
    }

    public function markReplied(string $id): void
    {
        Csrf::checkOrFail();
        ContactMessage::markReplied((int)$id, isset($_POST['is_replied']) ? 1 : 0);
        $this->flash('success', 'Reply status updated.');
        $this->back(url('admin/contacts/' . $id));
    }

    public function addNote(string $id): void
    {
        Csrf::checkOrFail();
        ContactMessage::addNote((int)$id, trim($_POST['admin_notes'] ?? ''));
        $this->flash('success', 'Note saved.');
        $this->redirect(url('admin/contacts/' . $id));
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        ContactMessage::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_contact', 'contact_message', (int)$id, 'Contact message deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Message deleted.');
        $this->redirect(url('admin/contacts'));
    }
}
