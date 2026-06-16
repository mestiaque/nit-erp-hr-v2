<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrFloorLine;

class HrFloorLineController extends Controller
{
    private const ENTITY = 'floor-lines';

    public function index(Request $request)
    {
        $query = HrFloorLine::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('floor_name', 'like', '%' . $search . '%')
                  ->orWhere('bn_floor_name', 'like', '%' . $search . '%')
                  ->orWhere('line_name', 'like', '%' . $search . '%')
                  ->orWhere('bn_line_name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20)->appends($request->query());
        $config = $this->entityConfig();

        return view('hr::masters.index', [
            'entityKey'    => self::ENTITY,
            'entity'       => $config,
            'items'        => $items,
            'request'      => $request,
            'options'      => $this->resolveOptions($config),
            'newItem'      => new HrFloorLine(),
            'useModalForm' => true,
        ]);
    }

    public function create()
    {
        $config = $this->entityConfig();

        return view('hr::masters.form', [
            'entityKey' => self::ENTITY,
            'entity'    => $config,
            'item'      => new HrFloorLine(),
            'options'   => $this->resolveOptions($config),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'floor_name'    => 'required|string|max:150',
            'bn_floor_name' => 'nullable|string|max:150',
            'line_name'     => 'required|string|max:150',
            'bn_line_name'  => 'nullable|string|max:150',
            'line_capacity' => 'nullable|integer|min:0',
            'status'        => 'required|in:active,inactive',
        ]);

        HrFloorLine::create($validated);

        return redirect()->route('hr-center.floor-lines.index')
            ->with('success', 'Block / Line created successfully.');
    }

    public function edit(int $id)
    {
        $config = $this->entityConfig();
        $item = HrFloorLine::findOrFail($id);

        return view('hr::masters.form', [
            'entityKey' => self::ENTITY,
            'entity'    => $config,
            'item'      => $item,
            'options'   => $this->resolveOptions($config),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = HrFloorLine::findOrFail($id);

        $validated = $request->validate([
            'floor_name'    => 'required|string|max:150',
            'bn_floor_name' => 'nullable|string|max:150',
            'line_name'     => 'required|string|max:150',
            'bn_line_name'  => 'nullable|string|max:150',
            'line_capacity' => 'nullable|integer|min:0',
            'status'        => 'required|in:active,inactive',
        ]);

        $item->update($validated);

        return redirect()->route('hr-center.floor-lines.index')
            ->with('success', 'Block / Line updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        HrFloorLine::findOrFail($id)->delete();

        return redirect()->route('hr-center.floor-lines.index')
            ->with('success', 'Block / Line deleted successfully.');
    }

    private function entityConfig(): array
    {
        return config('hr.entities.' . self::ENTITY);
    }

    private function resolveOptions(array $config): array
    {
        $options = [];

        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? '') === 'select' && isset($field['options'])) {
                $options[$name] = $field['options'];
            }
        }

        return $options;
    }
}
