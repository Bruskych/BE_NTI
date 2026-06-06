<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $study_program
 * @property int|null $year
 * @property array<array-key, mixed>|null $skills_json
 * @property string|null $cv_path
 * @property numeric|null $avg_grade
 * @property bool $has_carried_subjects
 * @property \Illuminate\Support\Carbon|null $eligibility_confirmed_at
 * @property string|null $eligibility_document_path
 * @property string|null $academic_documents_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereAcademicDocumentsPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereAvgGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereCvPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereEligibilityConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereEligibilityDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereHasCarriedSubjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereSkillsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereStudyProgram($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile withoutTrashed()
 * @mixin \Eloquent
 */
class StudentProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'study_program',
        'year',
        'skills_json',
        'cv_path',
        'avg_grade',
        'has_carried_subjects',
        'eligibility_confirmed_at',
        'eligibility_document_path',
        'academic_documents_path',
    ];

    protected $casts = [
        'skills_json'              => 'array',
        'avg_grade'                => 'decimal:2',
        'has_carried_subjects'     => 'boolean',
        'eligibility_confirmed_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
