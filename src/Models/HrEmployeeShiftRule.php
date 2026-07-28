<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEmployeeShiftRule extends BaseHrModel
{
    protected $table = 'hr_employee_shift_rules';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function primaryShift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'primary_shift_id');
    }

    /**
     * Ordered rotation of alternate shifts for the rule's day_of_week — sort_order
     * 0 is the shift on the first occurrence on/after anchor_date, sort_order 1 the
     * occurrence a week later, and so on, wrapping back to 0. See
     * HrEmployee::resolveShiftForDate() for how this list combines with primaryShift.
     */
    public function alternateShifts(): HasMany
    {
        return $this->hasMany(HrEmployeeShiftRuleAlternate::class, 'shift_rule_id')->with('shift')->orderBy('sort_order');
    }
}
