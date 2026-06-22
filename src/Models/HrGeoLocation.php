<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrGeoLocation extends BaseHrModel
{
    protected $table = 'hr_geo_locations';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getGrandparentNameAttribute(): ?string
    {
        return $this->parent?->parent?->name;
    }
}
