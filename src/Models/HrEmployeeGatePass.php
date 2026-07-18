<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeGatePass extends BaseHrModel
{
    protected $table = 'hr_employee_gate_pass';

    protected $casts = [
        'out_time' => 'datetime',
        'in_time'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
