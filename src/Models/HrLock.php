<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrLock extends BaseHrModel
{
    protected $table = 'hr_locks';

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'locked_by');
    }
}
