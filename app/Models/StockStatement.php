<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockStatement extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'items' => 'array',
            'age_analysis' => 'array',
            'other_info' => 'array',
            'linked_overrides' => 'array',
            'saved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
