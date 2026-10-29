<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teachers';
    protected $primaryKey = 'teacher_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'teacher_id',
        'user_id',
        'cv_document_id',
        'rejection_reason',
        'verified_levels',
        'wallet_balance',
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

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'requested_levels' => 'array',
            'approved_levels' => 'array',
            'verified_levels' => 'array',
            'wallet_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'cv_document_id', 'document_id');
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

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(TeacherAvailabilitySlot::class, 'teacher_id', 'teacher_id');
    }

    public function levelRequests(): HasMany
    {
        return $this->hasMany(TeacherLevelRequest::class, 'teacher_id', 'teacher_id');
    }

    public function learningSessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'teacher_id', 'teacher_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(TeacherPayout::class, 'teacher_id', 'teacher_id');
    }

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class, 'teacher_id', 'teacher_id');
    }

    public function tutorPackageRates(): HasMany
    {
        return $this->hasMany(TutorPackageRate::class, 'teacher_level', 'teacher_level');
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
