<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorPackageRate extends Model
{
    use HasFactory, HasUids;

    protected $table = 'tutor_package_rates';
    protected $primaryKey = 'rate_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'teacher_level',
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
}