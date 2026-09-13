<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasUids;

    protected $table = 'teachers';
    protected $primaryKey = 'teacher_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cv_url',
        'cv_status',
        'teacher_level',
        'default_location',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'teacher_id', 'teacher_id');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'teacher_id', 'teacher_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'teacher_id', 'teacher_id');
    }

    public function learningSchedules(): HasMany
    {
        return $this->hasMany(LearningSchedule::class, 'teacher_id', 'teacher_id');
    }

    public function digitalReports(): HasMany
    {
        return $this->hasMany(DigitalReport::class, 'teacher_id', 'teacher_id');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(TeacherWorkLog::class, 'teacher_id', 'teacher_id');
    }
}