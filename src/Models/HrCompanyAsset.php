<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A company/floor fixed-asset register row (furniture, electronics,
 * equipment) — location/department scoped, not tied to any one employee.
 * Distinct from HrEmployeeAsset, which is a per-employee issue/handover log.
 */
class HrCompanyAsset extends BaseHrModel
{
    protected $table = 'hr_company_assets';

    public const STATUSES = ['In Use', 'Under Repair', 'Disposed', 'Retired'];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrAssetCategory::class, 'asset_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function getTotalAcquisitionCostAttribute(): ?float
    {
        if ($this->unit_cost === null) {
            return null;
        }

        return round(((float) $this->unit_cost) * ((int) $this->quantity), 2);
    }

    public function getAgeYearsAttribute(): ?int
    {
        if (! $this->purchase_date) {
            return null;
        }

        return (int) $this->purchase_date->diffInYears(now());
    }
}
