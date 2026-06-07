<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use SoftDeletes, HasFactory;

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
