<?php

namespace ME\Hr\Models;

class HrRequisition extends BaseHrModel
{
    protected $table = 'hr_requisitions';

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function getDepartmentNameAttribute(): string
    {
        return $this->department->name ?? '-';
    }
}
