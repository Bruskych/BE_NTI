<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role', 'joined_at');
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function mentorships(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'mentor_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'mentor_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function gdprConsents(): HasMany
    {
        return $this->hasMany(GdprConsent::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function bulkMessagesSent(): HasMany
    {
        return $this->hasMany(BulkMessage::class, 'sender_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function approvedMilestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'approved_by');
    }

    // ---------------------------------------------------------
    // Role Helpers
    // ---------------------------------------------------------

    public function isStudent(): bool { return $this->hasRole('student'); }
    public function isTeamLeader(): bool { return $this->hasRole('team_leader'); }
    public function isCompany(): bool { return $this->hasRole('company'); }
    public function isMentor(): bool { return $this->hasRole('mentor'); }
    public function isEvaluator(): bool { return $this->hasRole('evaluator'); }
    public function isContentEditor(): bool { return $this->hasRole('content_editor'); }
    public function isAdmin(): bool { return $this->hasRole('admin') || $this->hasRole('super_admin'); }
    public function isSuperAdmin(): bool { return $this->hasRole('super_admin'); }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin', 'content_editor', 'evaluator', 'mentor']);
    }

    public function isManagement(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    // ---------------------------------------------------------
    // Accessors & Logic Helpers
    // ---------------------------------------------------------

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function isOwnerOf(Organization $organization): bool
    {
        return $this->organizations()
            ->where('organizations.id', $organization->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function belongsToOrg(Organization $organization): bool
    {
        return $this->organizations()
            ->where('organizations.id', $organization->id)
            ->exists();
    }
}
