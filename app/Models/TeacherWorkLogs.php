<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherWorkLog extends Model
{
    use HasFactory, HasUids;

    protected $table = 'teacher_work_logs';
    protected $primaryKey = 'log_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'session_id',
        'duration_minutes',
        'total_earnings',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'total_earnings' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id', 'session_id');
    }
}