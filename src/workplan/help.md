    $masterData = \App\Services\HrOptionsService::getOptions();
    $employeeData = \App\Services\HrOptionsService::getOptionsForEmployee();
    $employeeOthers = $employee->otherInfo();
    $jobType = $masterData['classifications']->where('id', $employee->employee_type)->first();
    $otRate = $basicForOt > 0 ? round(($basicForOt / 208) * 2, 2) : 0;