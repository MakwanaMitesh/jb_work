<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One step of the Admin-configured lead workflow: a Role, the Lead tabs that Role fills in at
 * this step ($modules), and earlier tabs it may look at read-only ($view_modules). Steps run in
 * ascending `sequence`; inactive steps are skipped when routing a lead forward. See
 * App\Services\LeadWorkflow for everything that reads this.
 */
class LeadWorkflowStage extends Model
{
    protected $fillable = [
        'name',
        'sequence',
        'role_id',
        'modules',
        'view_modules',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'modules' => 'array',
            'view_modules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'workflow_stage_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sequence')->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public function editableModules(): array
    {
        return array_values($this->modules ?? []);
    }

    /**
     * @return array<int, string>
     */
    public function readOnlyModules(): array
    {
        return array_values(array_diff($this->view_modules ?? [], $this->editableModules()));
    }
}
