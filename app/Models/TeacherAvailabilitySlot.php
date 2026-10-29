<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAvailabilitySlot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_availability_slots';
    protected $primaryKey = 'slot_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'slot_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'learning_method',
        'is_active',
        'is_available',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_available' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }
}
