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

    public function setOvertimeMinutesAttribute($value): void
    {
        $this->attributes['total_ot_minute'] = $value;
    }

    public function getInMinutesAttribute(): ?int
    {
        return $this->total_working_minute;
    }

    public function setInMinutesAttribute($value): void
    {
        $this->attributes['total_working_minute'] = $value;
    }
}
