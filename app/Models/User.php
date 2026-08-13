<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'nip',
        'email',
        'password',
        'role_id',
        'organization_unit_id',
        'simutu_id',
        'simutu_status',
        'simutu_synced_at',
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
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    public function department()
    {
        return $this->organizationUnit();
    }

    public function isAdmin()
    {
        // Berbasis permission dari database (bukan nama role).
        return $this->hasPermission('roles') && $this->hasPermission('users');
    }

    /**
     * Cek permission dari database melalui relasi role → permissions.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    /**
     * Apakah user bisa bertindak sebagai approver pada sebuah approval step.
     */
    public function canApprove(\App\Models\ApprovalStep $step): bool
    {
        if (! $step->approver_user_id) {
            return false;
        }

        return (int) $step->approver_user_id === (int) $this->id;
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(UserNotification::class)->unread();
    }
}
