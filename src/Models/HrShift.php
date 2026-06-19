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

    public function getCardAcceptFromAttribute()
    {
        return $this->card_accept_from ?? $this->start_allow_time;
    }

    public function getCardAcceptToAttribute()
    {
        return $this->card_accept_to ?? $this->end_time;
    }

    public function getCardAcceptToNextDayAttribute()
    {
        return $this->card_accept_to_next_day ?? false;
    }

    public function getShiftClosingTimeNextDayAttribute()
    {
        return $this->shift_closing_time_next_day ?? false;
    }

    public function getOverTimeAllowedUpToAttribute()
    {
        return $this->over_time_allowed_up_to ?? $this->out_time_start;
    }

    public function getOverTimeAllowedUpToNextDayAttribute()
    {
        return $this->over_time_allowed_up_to_next_day ?? false;
    }

    public function getOverTime1AllowedUpToAttribute()
    {
        return $this->over_time_1_allowed_up_to;
    }

    public function getOverTime1AllowedUpToNextDayAttribute()
    {
        return $this->over_time_1_allowed_up_to_next_day ?? false;
    }

    public function getWeeklyOvertimeAllowedAttribute()
    {
        return $this->weekly_overtime_allowed ?? true;
    }
}
