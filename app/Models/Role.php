<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Roles that cannot be deactivated or deleted — the system depends on them.
     *
     * @var list<string>
     */
    public const PROTECTED_ROLES = ['Admin'];

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED_ROLES, true);
    }
}
