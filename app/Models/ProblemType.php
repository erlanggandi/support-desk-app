<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProblemType extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
