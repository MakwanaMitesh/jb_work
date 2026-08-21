<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'city_id',
        'address',
        'status',
        'profile_photo_path',
        'qualification',
        'resume_path',
        'alternate_mobile_number',
        'aadhaar_card_number',
        'aadhaar_photo_path',
        'pan_card_number',
        'pan_photo_path',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_name',
        'bank_account_holder_name',
        'bank_cheque_photo_path',
    ];

    /**
     * Get the agent's full name.
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Check if agent is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the profile photo URL.
     */
    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function resumeUrl(): ?string
    {
        return $this->resume_path ? Storage::disk('public')->url($this->resume_path) : null;
    }

    public function aadhaarPhotoUrl(): ?string
    {
        return $this->aadhaar_photo_path ? Storage::disk('public')->url($this->aadhaar_photo_path) : null;
    }

    public function panPhotoUrl(): ?string
    {
        return $this->pan_photo_path ? Storage::disk('public')->url($this->pan_photo_path) : null;
    }

    public function bankChequePhotoUrl(): ?string
    {
        return $this->bank_cheque_photo_path ? Storage::disk('public')->url($this->bank_cheque_photo_path) : null;
    }

    /**
     * Get the city this agent belongs to.
     */
    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
