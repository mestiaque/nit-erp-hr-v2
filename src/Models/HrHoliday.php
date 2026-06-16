<?php

namespace ME\Hr\Models;

class HrHoliday extends BaseHrModel
{
    protected $table = 'hr_holidays';

    public function getTitleAttribute(): ?string
    {
        return $this->purpose;
    }

    public function getDaysAttribute(): int
    {
        if (!$this->from_date || !$this->to_date) {
            return 0;
        }

        return (int) \Carbon\Carbon::parse($this->from_date)->diffInDays(\Carbon\Carbon::parse($this->to_date)) + 1;
    }
}
