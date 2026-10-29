<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'student_id',
        'squad_id',
        'learning_method',
        'session_package',
        'selected_slot_id',
        'total_amount',
        'payment_status',
        'invoice_number',
        'xendit_invoice_id',
        'xendit_payment_url',
        'expires_at',
        'package_id',
        'teacher_id',
        'order_date',
        'order_status',
        'squad_code_used',
        'method',
        'learning_format',
        'session_package_count',
        'personal_amount',
        'discount_percentage',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'personal_amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'order_date' => 'datetime',
            'expires_at' => 'datetime',
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

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_id', 'squad_id');
    }

    public function selectedSlot(): BelongsTo
    {
        return $this->belongsTo(TeacherAvailabilitySlot::class, 'selected_slot_id', 'slot_id');
    }

    public function squadMember(): HasOne
    {
        return $this->hasOne(SquadMember::class, 'order_id', 'order_id');
    }

    public function learningSessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'order_id', 'order_id');
    }
}
