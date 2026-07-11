<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrRegularToWeekend extends BaseHrModel
{
    protected $table = 'hr_regular_to_weekends';

    public function getIsActiveAttribute(): int
    {
        return (int) $this->status;
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(HrSection::class, 'section_id');
    }
}
