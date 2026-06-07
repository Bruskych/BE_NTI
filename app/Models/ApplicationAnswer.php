<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $application_id
 * @property int $field_id
 * @property string|null $value_text
 * @property array<array-key, mixed>|null $value_json
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\FormField|null $field
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereValueJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereValueText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer withoutTrashed()
 * @mixin \Eloquent
 */
class ApplicationAnswer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'field_id',
        'value_text',
        'value_json',
        'file_path',
    ];

    protected $casts = [
        'value_json' => 'array',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
