<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $application_id
 * @property string|null $old_status
 * @property string|null $new_status
 * @property int|null $changed_by
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\User|null $changedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereOldStatus($value)
 * @mixin \Eloquent
 */
class ApplicationHistory extends Model
{
    protected $table = 'application_history';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'changed_by',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
