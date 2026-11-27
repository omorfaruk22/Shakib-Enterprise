<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Setting;
use App\Models\Page;
use App\Models\Admin;

class SettingController extends Controller
{
    public function index(): void
    {
        View::render('settings/index', [
            'title' => 'Website Settings | SAKIB ENTERPRISE',
            'settings' => Setting::all(),
            'aboutPage' => Page::findBySlug('about'),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function update(): void
    {
        Csrf::checkOrFail();

        // Allowlisted text keys that can be saved directly
        $textKeys = [
            'business_name','tagline','hero_title','hero_description','hero_cta_text',
            'about_title','about_content','mission','vision',
            'phone','whatsapp','email','address','maps_embed',
            'facebook','youtube','instagram','linkedin','opening_hours',
            'footer_text','meta_title','meta_description','logo_text',
            'homepage_services_title','homepage_services_subtitle',
            'homepage_destinations_title','homepage_destinations_subtitle',
            'homepage_tours_title','homepage_tours_subtitle',
            'homepage_holidays_title','homepage_holidays_subtitle',
            'homepage_whyus_title','homepage_whyus_subtitle',
            'homepage_process_title','homepage_process_subtitle',
            'homepage_testimonials_title','homepage_testimonials_subtitle',
            'homepage_cta_title','homepage_cta_text',
            'contact_page_title','contact_page_subtitle',
        ];

        $pairs = [];
        foreach ($textKeys as $k) {
            if (array_key_exists($k, $_POST)) {
                $pairs[$k] = trim($_POST[$k]);
            }
        }

        // Checkboxes
        $pairs['floating_whatsapp_enabled'] = isset($_POST['floating_whatsapp_enabled']) ? '1' : '0';
        $pairs['customer_login_enabled'] = isset($_POST['customer_login_enabled']) ? '1' : '0';

        // Normalize WhatsApp: keep digits only
        if (isset($pairs['whatsapp'])) {
            $pairs['whatsapp'] = preg_replace('/\D+/', '', $pairs['whatsapp']);
        }

        // About page
        if (isset($_POST['about_page_content'])) {
            Page::update('about', [
                'title' => trim($_POST['about_page_title'] ?? 'About Us'),
                'content' => trim($_POST['about_page_content']),
                'meta_title' => trim($_POST['about_page_meta_title'] ?? ''),
                'meta_description' => trim($_POST['about_page_meta_description'] ?? ''),
            ]);
        }

        Setting::updateMany($pairs);
        // Clear the static cache so the next request sees new values
        Setting::clearCache();

        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'update_settings', 'setting', null, 'Website settings updated', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'সেটিংস সেভ হয়েছে! ওয়েবসাইটে এখনই পরিবর্তন দেখা যাবে।');
        $this->redirect(url('/admin/settings'));
    }
}
