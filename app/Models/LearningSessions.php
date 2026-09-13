<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LearningSession extends Model
{
    use HasFactory, HasUids;

    protected $table = 'learning_sessions';
    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'schedule_id',
        'material_id',
        'session_status',
        'zoom_meeting_url',
        'onsite_address',
        'teacher_clock_in',
        'teacher_clock_out',
    ];

    protected function casts(): array
    {
        return [
            'teacher_clock_in' => 'datetime',
            'teacher_clock_out' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LearningSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'material_id', 'material_id');
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'session_id', 'session_id');
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'session_id', 'session_id');
    }

    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'session_id', 'session_id');
    }

    public function teacherWorkLog(): HasOne
    {
        return $this->hasOne(TeacherWorkLog::class, 'session_id', 'session_id');
    }
}