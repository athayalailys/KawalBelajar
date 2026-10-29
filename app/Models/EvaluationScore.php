<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'evaluation_scores';
    protected $primaryKey = 'evaluation_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'evaluation_id',
        'session_id',
        'student_id',
        'pretest_score',
        'posttest_score',
        'progress_percentage',
        'teacher_notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'pretest_score' => 'integer',
            'posttest_score' => 'integer',
            'progress_percentage' => 'decimal:2',
            'created_at' => 'datetime',
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
