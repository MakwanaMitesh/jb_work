<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'mobile_number',
        'city',
        'address',
        'joining_date',
        'profile_photo_path',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
        ];
    }

    /**
     * Rebuild the display `name` from the structured name parts.
     * Keeps the legacy `name` column (used across auth views, sessions,
     * nav, etc.) in sync whenever an employee profile is saved.
     */
    public function syncDisplayName(): void
    {
        $this->name = trim(collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->implode(' '));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    /**
     * Return all the permissions the user has via roles.
     *
     * Overrides Spatie's default so that permissions belonging only to
     * deactivated roles are excluded — a deactivated role grants nothing.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles
            ->where('is_active', true)
            ->flatMap(fn ($role) => $role->permissions)
            ->sort()
            ->values();
    }

    /**
     * Determine if the user has, via an active role, the given permission.
     *
     * Overrides Spatie's default so that a role which has been deactivated
     * no longer grants the permissions attached to it.
     */
    protected function hasPermissionViaRole(PermissionContract $permission): bool
    {
        $activeRoleIds = $this->roles->where('is_active', true)->pluck($this->roles()->getRelated()->getKeyName());

        return $permission->roles->pluck($permission->roles()->getRelated()->getKeyName())
            ->intersect($activeRoleIds)
            ->isNotEmpty();
    }

    /**
     * Effective, deduplicated permission names for this user
     * (role permissions from active roles + user-specific direct permissions).
     */
    public function effectivePermissionNames(): Collection
    {
        return $this->getAllPermissions()->pluck('name')->unique()->values();
    }
}
