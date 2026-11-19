<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorClassDraft extends Model
{
    protected $table = 'tutor_class_drafts';

    protected $primaryKey = 'teacher_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['teacher_id', 'package_id', 'draft_data'];

    protected function casts(): array
    {
        return ['draft_data' => 'array'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ModulePackage::class, 'package_id', 'package_id');
    }
}
