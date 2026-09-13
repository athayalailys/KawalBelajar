<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningMaterial extends Model
{
    use HasFactory, HasUids;

    protected $table = 'learning_materials';
    protected $primaryKey = 'material_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'package_id',
        'title',
        'material_type',
        'file_or_url',
        'video_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'video_duration_minutes' => 'integer',
            'created_at' => 'datetime',
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
        return $this->hasMany(LearningSession::class, 'material_id', 'material_id');
    }
}