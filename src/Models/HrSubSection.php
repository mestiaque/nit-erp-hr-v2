<?php

namespace ME\Hr\Models;

class HrSubSection extends BaseHrModel
{
    protected $table = 'hr_sub_sections';

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function section()
    {
        return $this->belongsTo(HrSection::class, 'section_id');
    }

    public function getDepartmentNameAttribute(): string
    {
        return $this->department->name ?? '-';
    }

    public function getSectionNameAttribute(): string
    {
        return $this->section->name ?? '-';
    }

    public function getSalaryTypeLabelAttribute(): string
    {
        return match ($this->salary_type) {
            'price_rate' => 'Price Rate',
            'fixed_rate' => 'Fixed Rate',
            default      => $this->salary_type ?? '-',
        };
    }
}
