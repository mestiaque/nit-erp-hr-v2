<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeNominee extends BaseHrModel
{
    protected $table = 'hr_employee_nominees';

    public function district(): BelongsTo
    {
        return $this->belongsTo(HrGeoLocation::class, 'district_id');
    }

    public function policeStation(): BelongsTo
    {
        return $this->belongsTo(HrGeoLocation::class, 'police_station_id');
    }
}
