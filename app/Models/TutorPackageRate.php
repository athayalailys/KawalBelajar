<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorPackageRate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_tariffs';
    protected $primaryKey = 'rate_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'teacher_level',
        'learning_method',
        'category_rate',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'category_rate' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id', 'admin_id');
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TariffAuditLog::class, 'tariff_id', 'rate_id');
    }
}
