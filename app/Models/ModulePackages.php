<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePackage extends Model
{
    use HasFactory, HasUids;

    protected $table = 'module_packages';
    protected $primaryKey = 'package_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'package_name',
        'grade_level',
        'description',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function tutorPackageRates(): HasMany
    {
        return $this->hasMany(TutorPackageRate::class, 'package_id', 'package_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'package_id', 'package_id');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'package_id', 'package_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'package_id', 'package_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'package_id', 'package_id');
    }

    public function learningSchedules(): HasMany
    {
        return $this->hasMany(LearningSchedule::class, 'package_id', 'package_id');
    }

    public function digitalReports(): HasMany
    {
        return $this->hasMany(DigitalReport::class, 'package_id', 'package_id');
    }
}