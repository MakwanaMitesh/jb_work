<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DprReport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'current_loans' => 'array',
            'machinery' => 'array',
            'collateral' => 'array',
            'financials' => 'array',
            'linked_overrides' => 'array',
            'saved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
