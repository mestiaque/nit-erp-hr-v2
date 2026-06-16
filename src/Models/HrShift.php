<?php

namespace ME\Hr\Models;

class HrShift extends BaseHrModel
{
    protected $table = 'hr_shifts';

    public function getNameOfShiftAttribute(): ?string
    {
        return $this->name;
    }

    public function getNameOfShiftBnAttribute(): ?string
    {
        return $this->bn_name;
    }

    public function getShiftStartingTimeAttribute()
    {
        return $this->start_time;
    }

    public function getShiftClosingTimeAttribute()
    {
        return $this->end_time;
    }

    public function getRedMarkingOnAttribute()
    {
        return $this->late_allow_time;
    }
}
