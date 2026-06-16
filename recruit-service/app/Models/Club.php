<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Club extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'description',
        'category',
        'member_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members')->withPivot('role')->withTimestamps();
    }

    public function leaders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members')
            ->wherePivot('role', 'leader')
            ->withTimestamps();
    }

    public function recruitmentPosts(): HasMany
    {
        return $this->hasMany(RecruitmentPost::class);
    }

    public function interviewSlots(): HasMany
    {
        return $this->hasMany(InterviewSlot::class);
    }
}
