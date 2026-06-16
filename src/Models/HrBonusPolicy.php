<?php

namespace ME\Hr\Models;

class HrBonusPolicy extends BaseHrModel
{
    protected $table = 'hr_bonus_policies';

    public function getNameAttribute(): ?string
    {
        return $this->policy_name;
    }

    public function getBnNameAttribute(): ?string
    {
        return $this->bn_policy_name;
    }

    public function getMonthFromAttribute(): ?int
    {
        return $this->month_range_from;
    }

    public function getMonthToAttribute(): ?int
    {
        return $this->month_range_to;
    }

    public function getAmountTypeAttribute(): ?string
    {
        return $this->type;
    }

    public function getSalaryBasisAttribute(): ?string
    {
        return $this->apply_on;
    }
}
