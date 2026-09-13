<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, HasUids;

    protected $table = 'students';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'parent_id',
        'education_level',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id', 'parent_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'student_id', 'student_id');
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'student_id', 'student_id');
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'student_id', 'student_id');
    }

    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'student_id', 'student_id');
    }

    public function digitalReports(): HasMany
    {
        return $this->hasMany(DigitalReport::class, 'student_id', 'student_id');
    }
}