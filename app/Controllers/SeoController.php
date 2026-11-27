<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\TourPackage;
use App\Models\HolidayPackage;

class SeoController extends Controller
{
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $base = rtrim(getenv('APP_URL') ?: url(''), '/');
        $urls = [
            ['', '1.0', 'daily'],
            ['air-ticket', '0.9', 'weekly'],
            ['ship-ticket', '0.9', 'weekly'],
            ['bus-ticket', '0.9', 'weekly'],
            ['tours', '0.9', 'weekly'],
            ['holiday-packages', '0.9', 'weekly'],
            ['about', '0.8', 'monthly'],
            ['contact', '0.8', 'monthly'],
        ];
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$path, $priority, $freq]) {
            echo "  <url><loc>{$base}/{$path}</loc><priority>{$priority}</priority><changefreq>{$freq}</changefreq></url>\n";
        }
        try {
            foreach (TourPackage::active(100) as $t) {
                echo "  <url><loc>{$base}/tours/" . urlencode($t['slug']) . "</loc><priority>0.8</priority><changefreq>weekly</changefreq></url>\n";
            }
            foreach (HolidayPackage::active(100) as $h) {
                echo "  <url><loc>{$base}/holiday-packages/" . urlencode($h['slug']) . "</loc><priority>0.8</priority><changefreq>weekly</changefreq></url>\n";
            }
        } catch (\Throwable $e) {
            // DB may not be ready — still output base URLs
        }
        echo '</urlset>';
    }
}
