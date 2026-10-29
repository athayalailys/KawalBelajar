<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMember extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'class_members';
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['member_id', 'squad_id', 'package_id', 'student_id', 'membership_role', 'joined_at', 'membership_status'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_id', 'squad_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
