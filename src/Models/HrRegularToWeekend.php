<?php

namespace ME\Hr\Models;

class HrRegularToWeekend extends BaseHrModel
{
    protected $table = 'hr_regular_to_weekends';

    public function getIsActiveAttribute(): int
    {
        return (int) $this->status;
    }
}
