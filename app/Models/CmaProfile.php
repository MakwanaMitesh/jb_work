<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmaProfile extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'linked_overrides' => 'array',
            'saved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
