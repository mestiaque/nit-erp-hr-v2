<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Traits\ConvertsBengaliNumerals;

class EmployeeBasicInfoReportController extends Controller
{
    use ConvertsBengaliNumerals;

    public function index(Request $request)
    {
        $bangla = $this->isBangla($request);

        $employees = HrEmployee::query()
            ->with(['designation:id,name,bn_name', 'department:id,name,bn_name'])
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'name', 'bn_name', 'designation_id', 'department_id', 'join_date']);

        $rows = $employees->map(fn (HrEmployee $employee) => [
            'employee_id'  => $bangla ? en2bnNumber($employee->employee_id) : $employee->employee_id,
            'name'         => $this->localizeName($employee->bn_name, $employee->name, $bangla),
            'designation'  => $this->localizeName($employee->designation?->bn_name, $employee->designation?->name, $bangla),
            'department'   => $this->localizeName($employee->department?->bn_name, $employee->department?->name, $bangla),
            'joining_date' => $this->localizeDate($employee->joining_date, $bangla),
        ]);

        return view('hr::reports.employee-basic-info', [
            'rows'   => $rows,
            'bangla' => $bangla,
        ]);
    }
}
