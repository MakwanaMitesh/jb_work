<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpinionReport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'assets_date' => 'date',
            'deponent_date' => 'date',
            'properties' => 'array',
            'liquid_assets' => 'array',
            'family_members' => 'array',
            'linked_overrides' => 'array',
            'saved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
