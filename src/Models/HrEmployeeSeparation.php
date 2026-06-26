<?php

namespace ME\Hr\Models;

class HrEmployeeSeparation extends BaseHrModel
{
    protected $table = 'hr_employee_separations';

    public function employee()
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
