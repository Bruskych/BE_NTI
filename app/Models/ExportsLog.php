<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportsLog extends Model
{
    public $timestamps = false;

    protected $table = 'exports_log';

    protected $fillable = [
        'user_id',
        'export_type',
        'filters_json',
        'file_path',
        'created_at',
    ];

    protected $casts = [
        'filters_json' => 'array',
        'created_at'   => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
