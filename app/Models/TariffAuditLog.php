<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tariff_audit_logs';
    protected $primaryKey = 'audit_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['audit_id', 'tariff_id', 'admin_id', 'old_price', 'new_price', 'justification_notes', 'changed_at'];

    protected function casts(): array
    {
        return ['old_price' => 'decimal:2', 'new_price' => 'decimal:2', 'changed_at' => 'datetime'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(MasterTariff::class, 'tariff_id', 'tariff_id');
    }
}
