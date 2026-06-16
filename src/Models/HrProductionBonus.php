<?php

namespace ME\Hr\Models;

class HrProductionBonus extends BaseHrModel
{
    protected $table = 'hr_production_bonuses';

    public function section()
    {
        return $this->belongsTo(HrSection::class, 'section_id');
    }

    public function getSectionNameAttribute(): string
    {
        return $this->section->name ?? '-';
    }
}
