<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrDisciplinaryNotice extends BaseHrModel
{
    protected $table = 'hr_disciplinary_notices';

    public const NOTICE_LABELS = [
        1 => '১ম নোটিশ',
        2 => '২য় নোটিশ',
        3 => '৩য় নোটিশ (চূড়ান্ত)',
    ];

    protected $casts = [
        'notice_date' => 'date',
        'incident_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function getNoticeLabelAttribute(): string
    {
        return self::NOTICE_LABELS[$this->notice_no] ?? ($this->notice_no . 'th Notice');
    }
}
