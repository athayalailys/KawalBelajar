<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LearningSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'learning_sessions';
    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'session_id',
        'student_id',
        'teacher_id',
        'schedule_id',
        'material_id',
        'session_number',
        'scheduled_at',
        'duration_minutes',
        'learning_method',
        'session_status',
        'status',
        'zoom_meeting_url',
        'onsite_address',
        'student_address',
        'latitude',
        'longitude',
        'address_landmark',
        'teacher_clock_in',
        'teacher_clock_out',
        'absensi_masuk_guru',
        'absensi_keluar_guru',
    ];

    protected function casts(): array
    {
        return [
            'teacher_clock_in' => 'datetime',
            'teacher_clock_out' => 'datetime',
            'absensi_masuk_guru' => 'datetime',
            'absensi_keluar_guru' => 'datetime',
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LearningSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('session_status', 'in_progress');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where(function ($builder) {
            $builder->where('session_status', 'scheduled')->orWhere('status', 'scheduled');
        });
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

    public function evaluationScore(): HasOne
{
    return $this->hasOne(EvaluationScore::class, 'session_id', 'session_id');
}

    public function teacherWorkLog(): HasOne
    {
        return $this->hasOne(TeacherWorkLog::class, 'session_id', 'session_id');
    }

    public function scheduledStartAt(): ?Carbon
    {
        $value = $this->scheduled_at ?? $this->schedule?->start_time;

        return $value ? Carbon::parse($value) : null;
    }

    public function hasStarted(): bool
    {
        $scheduledStart = $this->scheduledStartAt();

        return $scheduledStart !== null && $scheduledStart->lessThanOrEqualTo(now());
    }
}
