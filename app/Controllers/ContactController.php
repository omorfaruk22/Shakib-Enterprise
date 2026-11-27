<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Validator;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(): void
    {
        Csrf::checkOrFail();
        if (!empty($_POST['website'] ?? '')) {
            $this->redirect(url('contact'));
        }
        $data = $_POST;
        $errors = Validator::required($data, ['name', 'phone', 'subject', 'message']);
        if (!empty($data['phone']) && !Validator::phone($data['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }
        if (!empty($data['email']) && !Validator::email($data['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($errors) {
            $this->flash('error', 'Please fill all required fields correctly.');
            $this->old($data);
            $_SESSION['form_errors'] = $errors;
            $this->back(url('contact'));
        }
        try {
            ContactMessage::create([
                'name' => trim($data['name']),
                'phone' => trim($data['phone']),
                'email' => trim($data['email'] ?? ''),
                'subject' => trim($data['subject'] ?? ''),
                'message' => trim($data['message']),
            ]);
            clear_old();
            $this->flash('success', 'Thank you! Your message has been sent. We will contact you soon.');
            $this->redirect(url('contact'));
        } catch (\Throwable $e) {
            \App\Core\Logger::error('Contact save failed: ' . $e->getMessage());
            $this->flash('error', 'Something went wrong. Please try again.');
            $this->old($data);
            $this->back(url('contact'));
        }
    }
}
