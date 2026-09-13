<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningSchedule extends Model
{
    use HasFactory, HasUids;

    protected $table = 'learning_schedules';
    protected $primaryKey = 'schedule_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'package_id',
        'method',
        'location',
        'start_time',
        'end_time',
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
}