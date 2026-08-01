<?php

namespace ME\Hr\Models;

class HrAttendanceReplaceOff extends BaseHrModel
{
    protected $table = 'hr_attendance_replace_offs';

    protected $casts = [
        'worked_date' => 'date',
        'replace_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
