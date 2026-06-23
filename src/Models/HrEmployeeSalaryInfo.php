<?php

namespace ME\Hr\Models;

class HrEmployeeSalaryInfo extends BaseHrModel
{
    protected $table = 'hr_employee_salary_infos';

    public function getSalaryInfoStatusAttribute(): string
    {
        return $this->status ? 'active' : 'inactive';
    }
}
