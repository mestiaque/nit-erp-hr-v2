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

    public const DEPRECIATION_METHODS = ['Straight-Line'];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrAssetCategory::class, 'asset_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(HrAssetLocation::class, 'location_id');
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

    /**
     * Straight-line: (acquisition cost - salvage value) / useful life, per
     * year — the only method offered for now (DEPRECIATION_METHODS), so this
     * doesn't branch on $this->depreciation_method.
     */
    public function getAnnualDepreciationAttribute(): ?float
    {
        $cost = $this->total_acquisition_cost;
        if ($cost === null || ! $this->useful_life_years) {
            return null;
        }

        $depreciableBase = max(0, $cost - (float) ($this->salvage_value ?? 0));

        return round($depreciableBase / $this->useful_life_years, 2);
    }

    /**
     * Annual depreciation x age, capped at the fully-depreciated point (cost
     * minus salvage) so a very old asset never shows more depreciation than
     * it actually cost.
     */
    public function getAccumulatedDepreciationAttribute(): ?float
    {
        $annual = $this->annual_depreciation;
        if ($annual === null || $this->age_years === null) {
            return null;
        }

        $depreciableBase = max(0, $this->total_acquisition_cost - (float) ($this->salvage_value ?? 0));

        return round(min($annual * $this->age_years, $depreciableBase), 2);
    }

    public function getNetBookValueAttribute(): ?float
    {
        $cost = $this->total_acquisition_cost;
        if ($cost === null) {
            return null;
        }

        return round($cost - ($this->accumulated_depreciation ?? 0), 2);
    }
}
