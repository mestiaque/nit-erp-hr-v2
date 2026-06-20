<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeDocument extends BaseHrModel
{
    protected $table = 'hr_employee_documents';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type) === 'pdf';
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
