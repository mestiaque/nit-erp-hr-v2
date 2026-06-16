<?php

namespace ME\Hr\Models;

class HrAttendance extends BaseHrModel
{
    protected $table = 'hr_attendances';

    public function getUserIdAttribute(): ?int
    {
        return $this->employee_id;
    }

    public function getOvertimeMinutesAttribute(): ?int
    {
        return $this->total_ot_minute;
    }
}
