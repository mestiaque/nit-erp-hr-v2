<?php

namespace ME\Hr\Models;

class HrDesignation extends BaseHrModel
{
    protected $table = 'hr_designations';

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function getDepartmentNameAttribute(): string
    {
        return $this->department->name ?? '-';
    }

    public function getGradeIdAttribute(): ?string
    {
        return $this->grade;
    }

    public function getCarFuelAttribute()
    {
        return $this->car_fuel_allowance;
    }

    public function getPhoneInternetAttribute()
    {
        return $this->phone_internet_allowance;
    }

    public function getMinimumTiffinHourAttribute()
    {
        return $this->min_tiffin_hour;
    }

    public function getMinimumNightHourAttribute()
    {
        return $this->min_night_hour;
    }

    public function getMinimumDinnerHourAttribute()
    {
        return $this->min_dinner_hour;
    }

    public function getMealPaymentWayAttribute()
    {
        return $this->payment_way;
    }
}
