<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrShiftRosterEmployee extends BaseHrModel
{
    protected $table = 'hr_shift_roster_employees';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }

    public function getDateAttribute()
    {
        return $this->roster_date;
    }

    public function getSectionAttribute()
    {
        $employee = $this->getRelationValue('employee') ?? $this->employee;

        return $employee && $employee->section_id
            ? HrSection::query()->find($employee->section_id)
            : null;
    }

    public function getSubSectionAttribute()
    {
        $employee = $this->getRelationValue('employee') ?? $this->employee;

        return $employee && $employee->sub_section_id
            ? HrSubSection::query()->find($employee->sub_section_id)
            : null;
    }
}
