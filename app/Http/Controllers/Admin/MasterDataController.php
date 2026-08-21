<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\ProblemType;
use App\Models\Technician;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ponytail: CRUD generik 5 master data lewat satu controller + config;
 * pecah per-entity controller jika kebutuhan mulai berbeda jauh.
 */
class MasterDataController extends Controller
{
    private const CONFIG = [
        'departments' => [
            'model' => Department::class,
            'label' => 'Departemen',
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'], 'rules' => ['required', 'in:active,inactive']],
            ],
        ],
        'categories' => [
            'model' => Category::class,
            'label' => 'Kategori',
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'], 'rules' => ['required', 'in:active,inactive']],
            ],
        ],
        'problem-types' => [
            'model' => ProblemType::class,
            'label' => 'Tipe Masalah',
            'fields' => [
                ['name' => 'category_id', 'label' => 'Kategori', 'type' => 'select', 'options_from' => 'categories', 'rules' => ['required', 'integer', 'exists:categories,id']],
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'], 'rules' => ['required', 'in:active,inactive']],
            ],
        ],
        'priorities' => [
            'model' => Priority::class,
            'label' => 'Prioritas',
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'ordering', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
                // ponytail: sla_target_hours nullable sampai SLA diputuskan bisnis [TBD]
                ['name' => 'sla_target_hours', 'label' => 'Target SLA (jam)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'], 'rules' => ['required', 'in:active,inactive']],
            ],
        ],
        'technicians' => [
            'model' => Technician::class,
            'label' => 'Teknisi',
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'team', 'label' => 'Tim', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'contact', 'label' => 'Kontak', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'], 'rules' => ['required', 'in:active,inactive']],
            ],
        ],
    ];

    public function index(string $entity): Response
    {
        $config = self::config($entity);
        $model = $config['model'];

        return Inertia::render('admin/master/index', [
            'entity' => $entity,
            'label' => $config['label'],
            'fields' => $config['fields'],
            'items' => $model::query()
                ->when($entity === 'problem-types', fn ($q) => $q->with('category:id,name'))
                ->orderBy('id')
                ->get(),
            'options' => $this->dynamicOptions($config['fields']),
        ]);
    }

    public function store(Request $request, string $entity): RedirectResponse
    {
        $config = self::config($entity);
        $data = $request->validate(self::rules($config['fields']));

        $item = $config['model']::create($data);

        AuditLog::record('master_data.created', $entity, $item->id, $data);

        return back();
    }

    public function update(Request $request, string $entity, int $itemId): RedirectResponse
    {
        $config = self::config($entity);
        $item = $config['model']::findOrFail($itemId);

        $data = $request->validate(self::rules($config['fields']));
        $item->update($data);

        AuditLog::record('master_data.updated', $entity, $item->id, $data);

        return back();
    }

    public function destroy(string $entity, int $itemId): RedirectResponse
    {
        $config = self::config($entity);
        $item = $config['model']::findOrFail($itemId);

        AuditLog::record('master_data.deleted', $entity, $item->id, $item->only(array_column($config['fields'], 'name')));
        $item->delete();

        return back();
    }

    /**
     * @return array{model: class-string, label: string, fields: list<array<string, mixed>>}
     */
    private static function config(string $entity): array
    {
        abort_unless(isset(self::CONFIG[$entity]), 404);

        return self::CONFIG[$entity];
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private static function rules(array $fields): array
    {
        return collect($fields)
            ->mapWithKeys(fn (array $field): array => [$field['name'] => $field['rules']])
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, list<array{id: int, name: string}>>
     */
    private function dynamicOptions(array $fields): array
    {
        $options = [];

        foreach ($fields as $field) {
            if (($field['options_from'] ?? null) === 'categories') {
                $options[$field['name']] = Category::orderBy('name')->get(['id', 'name'])->toArray();
            }
        }

        return $options;
    }
}
