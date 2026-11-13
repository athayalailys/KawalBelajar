<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentModuleDownload extends Model
{
    use HasUuids;

    protected $table = 'student_module_downloads';
    protected $primaryKey = 'download_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['download_id', 'student_id', 'session_id', 'material_id', 'downloaded_at'];

    protected function casts(): array
    {
        return ['downloaded_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id', 'session_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'material_id', 'material_id');
    }
}
