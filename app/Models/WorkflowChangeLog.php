<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowChangeLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description'];

    public static function record(?int $userId, string $action, string $description): self
    {
        return self::create(['user_id' => $userId, 'action' => $action, 'description' => $description]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
