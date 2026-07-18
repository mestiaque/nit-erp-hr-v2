<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeAsset extends BaseHrModel
{
    protected $table = 'hr_employee_assets';

    protected $casts = [
        'accessories'           => 'array',
        'purpose_of_issue'      => 'array',
        'issued_date'           => 'date',
        'expected_return_date'  => 'date',
        'return_date'           => 'date',
        'purchase_value'        => 'decimal:2',
        'damage_cost'           => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrAssetCategory::class, 'asset_category_id');
    }
}
