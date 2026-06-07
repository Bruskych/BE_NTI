<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditEvent extends Model
{
    use SoftDeletes, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'object_type',
        'object_id',
        'old_values_json',
        'new_values_json',
        'ip_address',
        'user_agent',
        'result',
        'created_at',
    ];

    protected $casts = [
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'created_at'      => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isSuccessful(): bool
    {
        return $this->result === 'success';
    }
}
