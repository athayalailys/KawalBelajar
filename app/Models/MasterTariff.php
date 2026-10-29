<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterTariff extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'master_tariffs';
    protected $primaryKey = 'tariff_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'tariff_id',
        'grade_level',
        'method',
        'base_rate',
        'created_by_admin_id',
        'created_at',
        'rate_id',
        'package_id',
        'teacher_level',
        'learning_method',
        'category_rate',
    ];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'category_rate' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id', 'admin_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }
}
