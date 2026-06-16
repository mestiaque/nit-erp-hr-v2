<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeLeave extends BaseHrModel
{
    protected $table = 'hr_employee_leaves';

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(HrLeaveInfo::class, 'leave_type_id');
    }

    public function getStartDateAttribute()
    {
        return $this->leave_from;
    }

    public function setStartDateAttribute($value): void
    {
        $this->attributes['leave_from'] = $value;
    }

    public function getEndDateAttribute()
    {
        return $this->leave_to;
    }

    public function setEndDateAttribute($value): void
    {
        $this->attributes['leave_to'] = $value;
    }
}
