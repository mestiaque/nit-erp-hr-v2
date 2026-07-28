<?php

namespace ME\Hr\Http\Controllers\api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrEmployee;

class EmployeeApiController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = HrEmployee::query()
            ->with([
                'department:id,name',
                'designation:id,name',
                'section:id,name',
            ])
            ->select(['id', 'employee_id', 'name', 'department_id', 'designation_id', 'section_id'])
            ->orderBy('id')
            ->get()
            ->map(fn (HrEmployee $employee) => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'department' => $employee->department->name ?? null,
                'designation' => $employee->designation->name ?? null,
                'section' => $employee->section->name ?? null,
            ]);

        return response()->json([
            'success' => true,
            'count' => $employees->count(),
            'data' => $employees,
        ]);
    }
}
