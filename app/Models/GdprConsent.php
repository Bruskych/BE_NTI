<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Модель записи согласия пользователя с политикой GDPR */
class GdprConsent extends Model
{
    use HasFactory;

    public $timestamps = false;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'user_id',
        'consent_type',
        'version',
        'accepted_at',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
