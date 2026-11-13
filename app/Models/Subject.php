<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['subject_id', 'name', 'grade_level', 'category', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
