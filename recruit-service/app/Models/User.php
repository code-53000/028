<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'student_id',
        'major',
        'grade',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_members')->withPivot('role')->withTimestamps();
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function ledClubs()
    {
        return $this->belongsToMany(Club::class, 'club_members')
            ->wherePivot('role', 'leader')
            ->withTimestamps();
    }

    public function isClubLeader()
    {
        return $this->role === 'club_leader';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}
