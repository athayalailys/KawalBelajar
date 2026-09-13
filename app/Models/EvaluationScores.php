<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    use HasFactory, HasUids;

    protected $table = 'evaluation_scores';
    protected $primaryKey = 'evaluation_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'student_id',
        'pretest_score',
        'posttest_score',
        'progress_percentage',
        'teacher_notes',
    ];

    protected function casts(): array
    {
        return [
            'pretest_score' => 'integer',
            'posttest_score' => 'integer',
            'progress_percentage' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id', 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}