<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeAddress extends BaseHrModel
{
    protected $table = 'hr_employee_addresses';

    public function district(): BelongsTo
    {
        return $this->belongsTo(HrGeoLocation::class, 'district_id');
    }

    public function policeStation(): BelongsTo
    {
        return $this->belongsTo(HrGeoLocation::class, 'police_station_id');
    }
}
