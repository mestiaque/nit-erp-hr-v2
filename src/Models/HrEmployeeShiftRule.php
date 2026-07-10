<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeShiftRule extends BaseHrModel
{
    protected $table = 'hr_employee_shift_rules';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function altShift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'alt_shift_id');
    }

    public function primaryShift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'primary_shift_id');
    }
}
