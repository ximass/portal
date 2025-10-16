<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin',
        'enabled'
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
        ];
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Group::class,
            'id', // Foreign key on groups table
            'id', // Foreign key on permissions table
            'id', // Local key on users table
            'id'  // Local key on groups table
        )->join('group_user', 'groups.id', '=', 'group_user.group_id')
          ->join('group_permission', 'groups.id', '=', 'group_permission.group_id')
          ->where('group_user.user_id', $this->id)
          ->where('group_permission.permission_id', 'permissions.id')
          ->distinct();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->admin) {
            return true;
        }

        $permissions = Permission::whereHas('groups', function ($query) {
            $query->whereHas('users', function ($userQuery) {
                $userQuery->where('users.id', $this->id);
            });
        })->pluck('name')->toArray();

        return in_array($permissionName, $permissions);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->admin) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
