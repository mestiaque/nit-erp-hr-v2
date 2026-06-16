<?php

namespace ME\Hr\Models;

class HrSection extends BaseHrModel
{
    protected $table = 'hr_sections';

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function getDepartmentNameAttribute(): string
    {
        return $this->department->name ?? '-';
    }
}
