<?php

namespace App\Models;

use App\Support\ApplicationFormData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationForm extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        $casts = ['generated_at' => 'datetime', 'saved_at' => 'datetime', 'linked_overrides' => 'array'];

        foreach (ApplicationFormData::SCALARS as $key => $type) {
            if ($type === 'd') {
                $casts[$key] = 'date';
            }
        }

        foreach (ApplicationFormData::JSON_FIELDS as $key) {
            $casts[$key] = 'array';
        }

        return $casts;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
