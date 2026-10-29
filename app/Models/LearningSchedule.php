<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'learning_schedules';
    protected $primaryKey = 'schedule_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'package_id',
        'squad_id',
        'slot_id',
        'method',
        'location',
        'start_time',
        'end_time',
        'schedule_status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function learningSessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'schedule_id', 'schedule_id');
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_id', 'squad_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(TeacherAvailabilitySlot::class, 'slot_id', 'slot_id');
    }
}
