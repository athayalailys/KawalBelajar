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
    protected $casts = [
        'requested_levels' => 'array',
        'approved_levels' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'cv_url',
        'university',
        'study_program',
        'semester',
        'focus_subject',
        'requested_levels',
        'approved_levels',
        'identity_document_url',
        'transcript_url',
        'certificate_url',
        'video_link',
        'cv_status',
        'review_note',
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
