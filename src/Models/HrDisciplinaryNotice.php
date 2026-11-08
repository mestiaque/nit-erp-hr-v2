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

    /** Fine-notice texts: the incident lead-in is prefilled; the prior warning is only a placeholder hint. */
    public const FINE_DEFAULT_INCIDENT = 'আপনার দায়িত্বে অবহেলায় ও ভুল সিদ্ধান্ত গ্রহণ করার কারণে ';
    public const FINE_DEFAULT_PRIOR_WARNING = 'এর আগেও আপনাকে মৌখিকভাবে কাজের প্রতি যত্নশীল হওয়ার নির্দেশ দেওয়া হয়েছিল। কিন্তু আপনার কর্মপদ্ধতিতে কোনো পরিবর্তন লক্ষ্য করা যায়নি।';

    public static function fineDefaultSubject($days): string
    {
        return 'কাজে অবহেলার কারণে জরিমানা ও ' . en2bnNumber((string) ($days ?: 0)) . ' দিনের বেতন কর্তন প্রসঙ্গে।';
    }

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
