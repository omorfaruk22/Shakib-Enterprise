<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Page;

class PageController extends Controller
{
    public function about(): void
    {
        $page = Page::findBySlug('about');
        View::render('about', [
            'title' => $page['meta_title'] ?? 'About Us | SAKIB ENTERPRISE',
            'page' => $page,
        ], 'main');
    }

    public function contact(): void
    {
        View::render('contact', ['title' => 'Contact Us | SAKIB ENTERPRISE'], 'main');
    }
}
