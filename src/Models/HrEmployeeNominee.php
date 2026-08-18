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

    public function photoFile()
    {
        return $this->hasOne(\App\Models\File::class, 'fileable_id')
            ->where('fileable_type', self::class)
            ->where('use_case', 'photo');
    }

    public function photoUrl(): ?string
    {
        if ($this->photoFile) {
            return $this->photoFile->file_url;
        }

        return $this->photo ? asset($this->photo) : null;
    }
}
