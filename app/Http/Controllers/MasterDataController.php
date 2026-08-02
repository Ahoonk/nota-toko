<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);
        $modelClass = $definition['model'];

        $query = $modelClass::query()->latest('id');
        $this->applySearch($query, $request->string('q')->toString(), $definition['search'] ?? []);

        return view('masters.index', [
            'resource' => $resource,
            'definition' => $definition,
            'records' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);

        return view('masters.form', $this->formData($resource, $definition));
    }

    public function store(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);
        $modelClass = $definition['model'];

        $validated = $this->validateRequest($request, $definition);
        $validated = $this->persistFiles($request, $validated, $resource, true);

        if (array_key_exists('company_id', $validated) && empty($validated['company_id'])) {
            $validated['company_id'] = Company::query()->value('id');
        }

        /** @var Model $record */
        $record = $modelClass::create(array_merge($definition['defaults'] ?? [], $validated));
        $auditLogService->record($request, $record, 'created', [], $record->toArray());

        return redirect()->route("{$resource}.index")->with('status', "{$definition['title']} berhasil disimpan.");
    }

    public function edit(Request $request): View
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);
        $record = $this->resolveRecord($request, $definition);

        return view('masters.form', $this->formData($resource, $definition, $record));
    }

    public function update(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);
        $record = $this->resolveRecord($request, $definition);
        $oldValues = $record->toArray();

        $validated = $this->validateRequest($request, $definition, $record->getKey());
        $validated = $this->persistFiles($request, $validated, $resource, false, $record);
        $record->fill($validated)->save();

        $auditLogService->record($request, $record, 'updated', $oldValues, $record->toArray());

        return redirect()->route("{$resource}.index")->with('status', "{$definition['title']} berhasil diperbarui.");
    }

    public function destroy(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $resource = $this->resourceKey($request);
        $definition = $this->definition($resource);
        $record = $this->resolveRecord($request, $definition);
        $auditLogService->record($request, $record, 'deleted', $record->toArray(), []);
        $record->delete();

        return redirect()->route("{$resource}.index")->with('status', "{$definition['title']} berhasil dihapus.");
    }

    protected function resourceKey(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';

        return Str::before($routeName, '.');
    }

    protected function definition(string $resource): array
    {
        $definition = config("nota_toko.master_resources.{$resource}");

        abort_if(! $definition, 404, 'Resource tidak ditemukan.');

        return $definition;
    }

    protected function formData(string $resource, array $definition, ?Model $record = null): array
    {
        return [
            'resource' => $resource,
            'definition' => $definition,
            'record' => $record,
            'fields' => $definition['fields'] ?? [],
            'selectOptions' => $this->selectOptions(),
            'mode' => $record ? 'edit' : 'create',
        ];
    }

    protected function selectOptions(): array
    {
        return [
            'company_id' => Company::query()->orderBy('name')->pluck('name', 'id')->all(),
            'item_category_id' => ItemCategory::query()->orderBy('name')->pluck('name', 'id')->all(),
            'unit_id' => Unit::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    protected function validateRequest(Request $request, array $definition, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($definition['fields'] ?? [] as $field) {
            $fieldRules = [];

            $fieldRules[] = empty($field['required']) ? 'nullable' : 'required';

            switch ($field['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'mimes:jpg,jpeg,png,webp,pdf';
                    break;
                case 'select':
                    $fieldRules[] = 'integer';
                    $fieldRules[] = $this->existsRule($field['name']);
                    break;
                default:
                    $fieldRules[] = 'string';
                    break;
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $request->validate($rules);
    }

    protected function existsRule(string $fieldName): string
    {
        return match ($fieldName) {
            'company_id' => 'exists:companies,id',
            'item_category_id' => 'exists:item_categories,id',
            'unit_id' => 'exists:units,id',
            default => 'string',
        };
    }

    protected function persistFiles(Request $request, array $validated, string $resource, bool $isCreate, ?Model $record = null): array
    {
        foreach ($this->definition($resource)['fields'] as $field) {
            if (($field['type'] ?? null) !== 'file') {
                continue;
            }

            if ($request->hasFile($field['name'])) {
                $path = $request->file($field['name'])->store("master-data/{$resource}", 'public');
                $validated[$field['name']] = $path;
                continue;
            }

            if (! $isCreate && $record) {
                $validated[$field['name']] = $record->{$field['name']};
            }
        }

        return $validated;
    }

    protected function resolveRecord(Request $request, array $definition): Model
    {
        $modelClass = $definition['model'];
        $routeName = $request->route()?->getName() ?? '';
        $resource = Str::before($routeName, '.');
        $id = $request->route($resource) ?? $request->route('id');

        return $modelClass::query()->findOrFail($id);
    }

    protected function applySearch($query, string $searchTerm, array $columns): void
    {
        if ($searchTerm === '' || $columns === []) {
            return;
        }

        $query->where(function ($subQuery) use ($columns, $searchTerm) {
            foreach ($columns as $column) {
                $subQuery->orWhere($column, 'like', '%'.$searchTerm.'%');
            }
        });
    }
}
