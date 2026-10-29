<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPayout extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_payouts';
    protected $primaryKey = 'payout_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['payout_id', 'teacher_id', 'amount', 'bank_name', 'bank_account_number', 'bank_account_holder', 'disbursement_reference', 'payout_status', 'payout_date'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'payout_date' => 'datetime'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }
}
