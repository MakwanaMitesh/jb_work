<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Append-only workflow history row for a Lead. Rows can be created but never updated or deleted
 * through the application — the history must stay a faithful record of who handled what, when.
 */
class LeadTransfer extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_TRANSFERRED = 'transferred';
    public const ACTION_REASSIGNED = 'reassigned';
    public const ACTION_COMPLETED = 'completed';

    public const ACTION_LABELS = [
        self::ACTION_CREATED => 'Created',
        self::ACTION_TRANSFERRED => 'Transferred',
        self::ACTION_REASSIGNED => 'Reassigned',
        self::ACTION_COMPLETED => 'Completed',
    ];

    protected $fillable = [
        'lead_id',
        'action',
        'from_stage_id',
        'from_stage_name',
        'from_role_name',
        'from_user_id',
        'to_stage_id',
        'to_stage_name',
        'to_role_name',
        'to_user_id',
        'transferred_by',
        'remarks',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'transferred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Lead transfer history is permanent and cannot be modified.');
        });

        static::deleting(function () {
            throw new LogicException('Lead transfer history is permanent and cannot be deleted.');
        });
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst($this->action);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id')->withTrashed();
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id')->withTrashed();
    }

    public function transferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by')->withTrashed();
    }
}
