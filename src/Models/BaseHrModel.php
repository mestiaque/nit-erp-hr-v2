<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class BaseHrModel extends Model
{
    protected $guarded = ['id'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (Auth::check()) {
                if (empty($model->created_by) && $model->isFillable('created_by')) {
                    $model->created_by = Auth::id();
                }
                if (empty($model->updated_by) && $model->isFillable('updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function (self $model) {
            if (Auth::check() && $model->isFillable('updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
