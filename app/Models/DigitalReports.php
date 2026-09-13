<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalReport extends Model
{
    use HasFactory, HasUids;

    protected $table = 'digital_reports';
    protected $primaryKey = 'report_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'package_id',
        'teacher_id',
        'average_pretest_score',
        'average_posttest_score',
        'progress_percentage',
        'attendance_percentage',
        'teacher_notes',
        'report_status',
    ];

    protected function casts(): array
    {
        return [
            'average_pretest_score' => 'decimal:2',
            'average_posttest_score' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'attendance_percentage' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }
}