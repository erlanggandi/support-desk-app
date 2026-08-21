<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    protected $fillable = ['name', 'team', 'contact', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
