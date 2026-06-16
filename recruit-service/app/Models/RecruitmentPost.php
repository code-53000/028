<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentPost extends Model
{
    protected $fillable = [
        'club_id',
        'title',
        'description',
        'requirements',
        'benefits',
        'position_type',
        'quota',
        'status',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function interviewSlots(): HasMany
    {
        return $this->hasMany(InterviewSlot::class);
    }

    public function isOpen()
    {
        return $this->status === 'published' && (!$this->deadline || $this->deadline->isFuture());
    }
}
