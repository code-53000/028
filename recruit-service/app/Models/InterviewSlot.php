<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSlot extends Model
{
    protected $fillable = [
        'recruitment_post_id',
        'club_id',
        'start_time',
        'end_time',
        'location',
        'capacity',
        'booked_count',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function recruitmentPost(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPost::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function interviewResults(): HasMany
    {
        return $this->hasMany(InterviewResult::class);
    }

    public function isFull()
    {
        return $this->booked_count >= $this->capacity;
    }

    public function hasCapacity()
    {
        return $this->booked_count < $this->capacity;
    }
}
