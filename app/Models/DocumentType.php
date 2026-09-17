<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_required',
        'has_front_back',
        'allow_multiple',
        'allowed_file_types',
        'max_file_size_kb',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'has_front_back' => 'boolean',
        'allow_multiple' => 'boolean',
        'allowed_file_types' => 'array',
        'max_file_size_kb' => 'integer',
        'sort_order' => 'integer',
    ];

    public function leadDocuments(): HasMany
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getAllowedExtensionsStringAttribute(): string
    {
        if (empty($this->allowed_file_types) || !is_array($this->allowed_file_types)) {
            return 'pdf, jpg, jpeg, png';
        }
        return implode(', ', $this->allowed_file_types);
    }

    public function getAllowedMimesAttribute(): string
    {
        if (empty($this->allowed_file_types) || !is_array($this->allowed_file_types)) {
            return 'pdf,jpg,jpeg,png';
        }
        return implode(',', $this->allowed_file_types);
    }

    public function getFormattedMaxSizeAttribute(): string
    {
        if ($this->max_file_size_kb >= 1024) {
            return round($this->max_file_size_kb / 1024, 1) . ' MB';
        }
        return $this->max_file_size_kb . ' KB';
    }
}
