<?php

namespace ME\Hr\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use ME\Hr\Models\HrClassification as Classification;
use ME\Hr\Models\HrDepartment as Department;
use ME\Hr\Models\HrFloorLine as FloorLine;
use ME\Hr\Models\HrSection as Section;

class HrMasterController extends Controller
{
    public function index(Request $request, string $entity)
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];
        $query = $modelClass::latest();
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }
        $options = $this->resolveOptions($config);
        $useModalForm = count($config['fields'] ?? []) <= 8;

        foreach (($config['defaults'] ?? []) as $column => $value) {
            // Keep index flexible: status should be user-driven from filter dropdown.
            if ($column === 'name' || $column === 'status') {
                continue;
            }

            if ($this->hasColumn($modelClass, $column)) {
                $query->where($column, $value);
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request, $config) {
                foreach ($config['search'] ?? ['name'] as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', '%' . $request->search . '%');
                }
            });
        }

        if ($request->filled('status') && $this->hasColumn($modelClass, 'status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20)->appends($request->query());

        return view('hr::masters.index', [
            'entityKey' => $entity,
            'entity' => $config,
            'items' => $items,
            'request' => $request,
            'options' => $options,
            'newItem' => new $modelClass(),
            'useModalForm' => $useModalForm,
        ]);
    }

    public function create(string $entity)
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];

        return view('hr::masters.form', [
            'entityKey' => $entity,
            'entity' => $config,
            'item' => new $modelClass(),
            'options' => $this->resolveOptions($config),
        ]);
    }

    public function store(Request $request, string $entity): RedirectResponse
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];
        $payload = $this->validatedPayload($request, $config, $modelClass);

        $item = new $modelClass();
        $item->fill($payload);
        if ($this->hasColumn($modelClass, 'addedby_id')) {
            $item->addedby_id = Auth::id();
        }
        $item->save();

        if ($entity === 'bonus-titles' && $request->boolean('add_policy')) {
            return redirect()
                ->route('hr-center.masters.create', ['entity' => 'bonus-policies', 'bonus_title_id' => $item->id])
                ->with('success', $config['title'] . ' created successfully. Now add a related policy.');
        }

        return redirect()->route('hr-center.masters.index', $entity)->with('success', $config['title'] . ' created successfully.');
    }

    public function edit(string $entity, int $id)
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];
        $item = $modelClass::findOrFail($id);

        return view('hr::masters.form', [
            'entityKey' => $entity,
            'entity' => $config,
            'item' => $item,
            'options' => $this->resolveOptions($config),
        ]);
    }

    public function update(Request $request, string $entity, int $id): RedirectResponse
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];
        $item = $modelClass::findOrFail($id);
        $payload = $this->validatedPayload($request, $config, $modelClass);

        $item->fill($payload);
        if ($this->hasColumn($modelClass, 'editedby_id')) {
            $item->editedby_id = Auth::id();
        }
        $item->save();

        if ($entity === 'bonus-titles' && $request->boolean('add_policy')) {
            return redirect()
                ->route('hr-center.masters.create', ['entity' => 'bonus-policies', 'bonus_title_id' => $item->id])
                ->with('success', $config['title'] . ' updated successfully. Now add a related policy.');
        }

        return redirect()->route('hr-center.masters.index', $entity)->with('success', $config['title'] . ' updated successfully.');
    }

    public function destroy(string $entity, int $id): RedirectResponse
    {
        $config = $this->entityConfig($entity);
        $modelClass = $config['model'];
        $item = $modelClass::findOrFail($id);
        $item->delete();

        return redirect()->route('hr-center.masters.index', $entity)->with('success', $config['title'] . ' deleted successfully.');
    }

    private function entityConfig(string $entity): array
    {
        $config = config('hr.entities.' . $entity);
        abort_if(!$config, 404);

        return $config;
    }

    private function resolveOptions(array $config): array
    {
        $options = [];

        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? '') !== 'select') {
                continue;
            }

            if (isset($field['options'])) {
                $options[$name] = $field['options'];
                continue;
            }

            $source = $field['source'] ?? null;
            if (!$source) {
                $options[$name] = [];
                continue;
            }

            if ($source['driver'] === 'attribute') {
                $options[$name] = $this->resolveAttributeLikeOptions((string) ($source['filter'] ?? ''));
                continue;
            }

            if ($source['driver'] === 'user') {
                $label = $source['label'] ?? 'name';
                $options[$name] = User::query()->orderBy('name')->pluck($label, 'id')->toArray();
                continue;
            }

            if ($source['driver'] === 'model') {
                $modelClass = $source['model'];
                $label = $source['label'] ?? 'name';
                $query = $modelClass::query();
                foreach (($source['conditions'] ?? []) as $column => $value) {
                    $query->where($column, $value);
                }
                $options[$name] = $query->orderBy($label)->pluck($label, 'id')->toArray();
                continue;
            }

            $options[$name] = [];
        }

        return $options;
    }

    private function validatedPayload(Request $request, array $config, string $modelClass): array
    {
        $rules = [];
        $checkboxes = [];
        foreach ($config['fields'] as $name => $field) {
            $rules[$name] = $field['rules'] ?? 'nullable';
            if (($field['type'] ?? '') === 'checkbox') {
                $checkboxes[] = $name;
            }
        }

        $payload = $request->validate($rules);

        foreach ($checkboxes as $checkbox) {
            $payload[$checkbox] = $request->boolean($checkbox);
        }

        foreach (($config['defaults'] ?? []) as $column => $value) {
            if ($column !== 'name' && $this->hasColumn($modelClass, $column)) {
                $payload[$column] = $payload[$column] ?? $value;
            }
        }

        return $payload;
    }

    private function resolveAttributeLikeOptions(string $filter): array
    {
        return match ($filter) {
            'department', 'departments' => Department::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray(),
            'section', 'sections' => Section::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray(),
            'classification', 'classifications' => Classification::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray(),
            'line', 'lines' => FloorLine::query()
                ->where('status', 'active')
                ->orderBy('line_name')
                ->pluck('line_name', 'id')
                ->toArray(),
            default => [],
        };
    }

    private function hasColumn(string $modelClass, string $column): bool
    {
        return Schema::hasColumn((new $modelClass())->getTable(), $column);
    }
}
