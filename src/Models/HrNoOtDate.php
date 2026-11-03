<?php

namespace ME\Hr\Models;

/** A date on which nobody earns OT (see EmployeeAttendanceService::getEmployeeAttendanceByDate()). */
class HrNoOtDate extends BaseHrModel
{
    protected $table = 'hr_no_ot_dates';

    protected $casts = [
        'date' => 'date',
    ];

    /** ['Y-m-d' => true] for every no-OT date in the range. */
    public static function between(string $from, string $to): array
    {
        return static::query()
            ->whereBetween('date', [$from, $to])
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [\Carbon\Carbon::parse($d)->toDateString() => true])
            ->all();
    }
}
