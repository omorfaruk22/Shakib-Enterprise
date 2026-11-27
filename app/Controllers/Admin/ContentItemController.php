<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Admin;
use App\Models\WhyUsCard;
use App\Models\ProcessStep;
use App\Models\CompanyStat;

/**
 * Generic CMS controller for simple homepage-content items:
 *  - Why Us cards
 *  - Process Steps
 *  - Company Stats
 *
 * All three share the same pattern: icon + title/number + description/label, is_active, sort_order.
 */
class ContentItemController extends Controller
{
    private function map(string $section): array
    {
        $map = [
            'why-us' => [
                'model'   => WhyUsCard::class,
                'entity'  => 'why_us_card',
                'title'   => 'Why Us Cards',
                'singular'=> 'Card',
                'view'    => 'home-content',
                'fields'  => [
                    ['name'=>'icon','label'=>'Font Awesome Icon','type'=>'text','default'=>'fa-solid fa-check-circle','required'=>true,'placeholder'=>'fa-solid fa-shield-halved'],
                    ['name'=>'title','label'=>'Title','type'=>'text','default'=>'','required'=>true,'placeholder'=>'Trusted & Reliable'],
                    ['name'=>'description','label'=>'Description','type'=>'textarea','default'=>'','required'=>true,'placeholder'=>'Short paragraph...'],
                ],
                'hasNumber' => false,
                'hasStep' => false,
            ],
            'process-steps' => [
                'model'   => ProcessStep::class,
                'entity'  => 'process_step',
                'title'   => 'Process Steps',
                'singular'=> 'Step',
                'view'    => 'home-content',
                'fields'  => [
                    ['name'=>'title','label'=>'Step Title','type'=>'text','default'=>'','required'=>true,'placeholder'=>'Send Inquiry'],
                    ['name'=>'description','label'=>'Description','type'=>'textarea','default'=>'','required'=>true,'placeholder'=>'Step description...'],
                ],
                'hasNumber' => false,
                'hasStep' => true,
            ],
            'company-stats' => [
                'model'   => CompanyStat::class,
                'entity'  => 'company_stat',
                'title'   => 'Company Stats',
                'singular'=> 'Stat',
                'view'    => 'home-content',
                'fields'  => [
                    ['name'=>'icon','label'=>'Font Awesome Icon','type'=>'text','default'=>'fa-solid fa-users','required'=>true,'placeholder'=>'fa-solid fa-users'],
                    ['name'=>'number','label'=>'Number / Value','type'=>'text','default'=>'','required'=>true,'placeholder'=>'5000+'],
                    ['name'=>'label','label'=>'Label','type'=>'text','default'=>'','required'=>true,'placeholder'=>'Happy Customers'],
                ],
                'hasNumber' => true,
                'hasStep' => false,
            ],
        ];

        if (!isset($map[$section])) {
            throw new \RuntimeException('Unknown section: ' . $section);
        }
        return $map[$section];
    }

    private function basePath(string $section): string
    {
        return '/admin/' . $section;
    }

    public function index(string $section): void
    {
        $cfg = $this->map($section);
        $items = $cfg['model']::all();
        View::render('home-content/index', [
            'title'   => $cfg['title'] . ' | SAKIB ENTERPRISE',
            'section' => $section,
            'cfg'     => $cfg,
            'items'   => $items,
            'admin'   => Auth::user(),
        ], 'admin');
    }

    public function create(string $section): void
    {
        $cfg = $this->map($section);
        View::render('home-content/form', [
            'title'   => 'Add ' . $cfg['singular'] . ' | SAKIB ENTERPRISE',
            'section' => $section,
            'cfg'     => $cfg,
            'item'    => null,
            'admin'   => Auth::user(),
        ], 'admin');
    }

    public function store(string $section): void
    {
        Csrf::checkOrFail();
        $cfg = $this->map($section);
        $data = $this->sanitize($cfg, $_POST);
        try {
            $id = $cfg['model']::create($data);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'create_' . $cfg['entity'], $cfg['entity'], $id, $cfg['singular'] . ' created: ' . ($data['title'] ?? $data['label'] ?? ''), $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', $cfg['singular'] . ' added successfully.');
            $this->redirect(url($this->basePath($section)));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url($this->basePath($section) . '/create'));
        }
    }

    public function edit(string $section, string $id): void
    {
        $cfg = $this->map($section);
        $item = $cfg['model']::find((int)$id);
        if (!$item) { http_response_code(404); die($cfg['singular'] . ' not found.'); }
        View::render('home-content/form', [
            'title'   => 'Edit ' . $cfg['singular'] . ' | SAKIB ENTERPRISE',
            'section' => $section,
            'cfg'     => $cfg,
            'item'    => $item,
            'admin'   => Auth::user(),
        ], 'admin');
    }

    public function update(string $section, string $id): void
    {
        Csrf::checkOrFail();
        $cfg = $this->map($section);
        if (!$cfg['model']::find((int)$id)) { http_response_code(404); die('Not found.'); }
        $data = $this->sanitize($cfg, $_POST);
        try {
            $cfg['model']::update((int)$id, $data);
            $admin = Auth::user();
            Admin::logActivity((int)$admin['id'], 'update_' . $cfg['entity'], $cfg['entity'], (int)$id, $cfg['singular'] . ' updated', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->flash('success', $cfg['singular'] . ' updated successfully.');
            $this->redirect(url($this->basePath($section)));
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
            $this->back(url($this->basePath($section) . '/' . $id . '/edit'));
        }
    }

    public function destroy(string $section, string $id): void
    {
        Csrf::checkOrFail();
        $cfg = $this->map($section);
        $cfg['model']::delete((int)$id);
        $admin = Auth::user();
        Admin::logActivity((int)$admin['id'], 'delete_' . $cfg['entity'], $cfg['entity'], (int)$id, $cfg['singular'] . ' deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', $cfg['singular'] . ' deleted.');
        $this->redirect(url($this->basePath($section)));
    }

    private function sanitize(array $cfg, array $post): array
    {
        $data = [
            'icon'        => trim($post['icon'] ?? $cfg['fields'][0]['default'] ?? 'fa-solid fa-check'),
            'is_active'   => isset($post['is_active']) ? 1 : 0,
            'sort_order'  => (int)($post['sort_order'] ?? 0),
        ];
        foreach ($cfg['fields'] as $f) {
            $data[$f['name']] = trim($post[$f['name']] ?? $f['default'] ?? '');
        }
        if ($cfg['hasStep']) {
            $data['step_number'] = (int)($post['step_number'] ?? 1);
        }
        return $data;
    }
}
