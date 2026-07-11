<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeSalarySnapshot extends BaseHrModel
{
    protected $table = 'hr_employee_salary_snapshots';

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
