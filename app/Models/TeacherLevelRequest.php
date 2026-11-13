<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLevelRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_level_requests';
    protected $primaryKey = 'request_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['request_id', 'teacher_id', 'requested_level', 'supporting_document_id', 'supporting_document_path', 'request_status', 'rejection_reason', 'reviewed_by_admin_id', 'reviewed_at', 'created_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function supportingDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'supporting_document_id', 'document_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id', 'admin_id');
    }
}
