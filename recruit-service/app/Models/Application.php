<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'recruitment_post_id',
        'motivation',
        'experience',
        'skills',
        'status',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            //
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recruitmentPost(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPost::class);
    }

    public function interviewResults(): HasMany
    {
        return $this->hasMany(InterviewResult::class);
    }

    public function currentInterviewResult()
    {
        return $this->interviewResults()->latest()->first();
    }
}
