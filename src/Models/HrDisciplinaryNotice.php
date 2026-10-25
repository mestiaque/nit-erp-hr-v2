<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrDisciplinaryNotice extends BaseHrModel
{
    protected $table = 'hr_disciplinary_notices';

    public const TYPE_FINE = 'fine';
    public const TYPE_SHOW_CAUSE = 'show_cause';

    public const TYPES = [
        self::TYPE_FINE => 'Fine & Penalty',
        self::TYPE_SHOW_CAUSE => 'Show Cause',
    ];

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

    public function isShowCause(): bool
    {
        return $this->notice_type === self::TYPE_SHOW_CAUSE;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->notice_type] ?? self::TYPES[self::TYPE_FINE];
    }

    public function getNoticeLabelAttribute(): string
    {
        return self::NOTICE_LABELS[$this->notice_no] ?? ($this->notice_no . 'th Notice');
    }
}
