<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeShiftRuleAlternate extends BaseHrModel
{
    protected $table = 'hr_employee_shift_rule_alternates';

    public function shiftRule(): BelongsTo
    {
        return $this->belongsTo(HrEmployeeShiftRule::class, 'shift_rule_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }
}
