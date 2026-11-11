<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_banks';
    protected $primaryKey = 'question_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'teacher_id',
        'package_id',
        'session_number',
        'exam_type',
        'category',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'answer_key',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'question_id', 'question_id');
    }
}
