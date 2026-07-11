<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrLock extends BaseHrModel
{
    protected $table = 'hr_locks';

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'locked_by');
    }

    /**
     * True if $module is locked for the given year/month, either by a
     * whole-factory lock (department_id null) or one scoped to $departmentId.
     */
    public static function isLocked(
        string $module,
        int $year,
        int $month,
        ?int $departmentId = null,
        ?int $factoryId = null
    ): bool {
        return static::query()
            ->where('module', $module)
            ->where('lock_year', $year)
            ->where('lock_month', $month)
            ->where('is_locked', true)
            ->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', $departmentId);
            })
            ->where(function ($q) use ($factoryId) {
                $q->whereNull('factory_id')
                    ->orWhere('factory_id', $factoryId);
            })
            ->exists();
    }
}
