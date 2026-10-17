<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'New';
    public const STATUS_CONTACTED = 'Contacted';
    public const STATUS_FOLLOW_UP = 'Follow-up';
    public const STATUS_CONVERTED = 'Converted';
    public const STATUS_CLOSED = 'Closed';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_FOLLOW_UP => 'Follow-up',
        self::STATUS_CONVERTED => 'Converted',
        self::STATUS_CLOSED => 'Closed',
    ];

    protected $fillable = [
        'full_name',
        'mobile',
        'email',
        'city',
        'loan_requirement',
        'message',
        'status',
    ];

    /**
     * Get CSS badge class for status display.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'jb-badge-blue',
            self::STATUS_CONTACTED => 'jb-badge-amber',
            self::STATUS_FOLLOW_UP => 'jb-badge-purple',
            self::STATUS_CONVERTED => 'jb-badge-emerald',
            self::STATUS_CLOSED => 'jb-badge-slate',
            default => 'jb-badge-slate',
        };
    }
}
