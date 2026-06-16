<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDepartment extends BaseHrModel
{
    protected $table = 'hr_departments';

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'department_id');
    }
}
