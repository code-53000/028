<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewResult extends Model
{
    protected $fillable = [
        'application_id',
        'interview_slot_id',
        'interviewer_id',
        'score',
        'comment',
        'result',
        'round',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            //
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewSlot(): BelongsTo
    {
        return $this->belongsTo(InterviewSlot::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
