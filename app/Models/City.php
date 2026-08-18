<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Check if city is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get users belonging to this city.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get agents belonging to this city.
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
