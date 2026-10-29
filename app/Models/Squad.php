<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'squads';
    protected $primaryKey = 'squad_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'squad_id',
        'package_id',
        'target_quota',
        'current_members_count',
        'squad_status',
        'created_at',
        'target_capacity',
        'discount_rate',
        'status',
        'teacher_id',
        'selected_slot_id',
        'learning_method',
        'squad_code',
        'host_student_id',
    ];

    protected function casts(): array
    {
        return [
            'target_capacity' => 'integer',
            'discount_rate' => 'decimal:2',
        ];
    }

    public function hostStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'host_student_id', 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SquadMember::class, 'squad_id', 'squad_id');
    }

    public function classMembers(): HasMany
    {
        return $this->hasMany(ClassMember::class, 'squad_id', 'squad_id');
    }
}
