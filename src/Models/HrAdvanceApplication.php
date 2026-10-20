<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Salary advance application letter (record + print only - see its migration). */
class HrAdvanceApplication extends BaseHrModel
{
    protected $table = 'hr_advance_applications';

    public const DEFAULT_REASON = 'পারিবারিক ও ব্যক্তিগত';

    protected $casts = [
        'application_date' => 'date',
        'deduction_month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
