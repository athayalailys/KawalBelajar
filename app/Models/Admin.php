<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admins';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'user_id',
        'employee_id_number',
        'position',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function createdPackageRates(): HasMany
    {
        return $this->hasMany(TutorPackageRate::class, 'created_by_admin_id', 'admin_id');
    }

    public function tariffAuditLogs(): HasMany
    {
        return $this->hasMany(TariffAuditLog::class, 'admin_id', 'admin_id');
    }
}
