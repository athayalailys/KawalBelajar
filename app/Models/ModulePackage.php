<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModulePackage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'module_packages';
    protected $primaryKey = 'package_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'teacher_id',
        'package_name',
        'grade_level',
        'subject_name',
        'learning_method',
        'max_capacity',
        'description',
        'target_learning',
        'supported_session_packages',
        'is_published',
        'is_open',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'is_open' => 'boolean',
            'max_capacity' => 'integer',
        ];
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function tutorPackageRates(): HasMany
    {
        return $this->hasMany(TutorPackageRate::class, 'package_id', 'package_id');
    }

    public function classMembers(): HasMany
    {
        return $this->hasMany(ClassMember::class, 'package_id', 'package_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'package_id', 'package_id');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'package_id', 'package_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'package_id', 'package_id');
    }

    public function learningSchedules(): HasMany
    {
        return $this->hasMany(LearningSchedule::class, 'package_id', 'package_id');
    }

    public function availabilitySlots(): BelongsToMany
    {
        return $this->belongsToMany(
            TeacherAvailabilitySlot::class,
            'module_package_availability_slots',
            'package_id',
            'slot_id',
            'package_id',
            'slot_id'
        );
    }

    public function digitalReports(): HasMany
    {
        return $this->hasMany(DigitalReport::class, 'package_id', 'package_id');
    }

    public function plannedSessionCount(): int
    {
        $count = (int) ($this->supported_session_packages ?? 1);

        return in_array($count, [1, 4], true) ? $count : 1;
    }

    public function incompleteSessionNumbers(): array
    {
        $sessionNumbers = range(1, $this->plannedSessionCount());
        $contents = TutorClassSessionContent::query()->where('package_id', $this->package_id)
            ->whereNotNull('topic')->whereNotNull('learning_target')->get()->keyBy('session_number');
        $materials = $this->learningMaterials()->whereIn('session_number', $sessionNumbers)
            ->select('session_number')->distinct()->pluck('session_number')->map(fn ($number) => (int) $number)->all();
        $questionCounts = $this->questionBanks()->where('exam_type', 'pre_test')->whereIn('session_number', $sessionNumbers)
            ->selectRaw('session_number, count(*) as aggregate')->groupBy('session_number')->pluck('aggregate', 'session_number');

        return collect($sessionNumbers)->filter(fn (int $number) =>
            !filled($contents->get($number)?->topic)
            || !filled($contents->get($number)?->learning_target)
            || !in_array($number, $materials)
            || (int) $questionCounts->get($number, 0) < 3
        )->values()->all();
    }

    public function isReadyForEnrollment(): bool
    {
        return $this->incompleteSessionNumbers() === [];
    }
}
