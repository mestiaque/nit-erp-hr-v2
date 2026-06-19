<?php

namespace ME\Hr\Models;

class HrAttendanceMachineLog extends BaseHrModel
{
    protected $table = 'hr_attendance_machine_logs';

    protected $casts = [
        'log_time' => 'datetime',
    ];
}
