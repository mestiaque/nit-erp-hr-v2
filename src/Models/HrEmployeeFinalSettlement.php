<?php

namespace ME\Hr\Models;

class HrEmployeeFinalSettlement extends BaseHrModel
{
    protected $table = 'hr_employee_final_settlements';

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
