<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $export_type
 * @property array<array-key, mixed>|null $filters_json
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereExportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereFiltersJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereUserId($value)
 * @mixin \Eloquent
 */
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
