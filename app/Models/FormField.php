<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $program_id
 * @property int|null $call_id
 * @property string|null $name
 * @property string|null $label
 * @property string|null $type
 * @property bool $required
 * @property array<array-key, mixed>|null $options_json
 * @property string|null $validation_rules
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Call|null $call
 * @property-read \App\Models\Program|null $program
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereOptionsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereValidationRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField withoutTrashed()
 * @mixin \Eloquent
 */
class FormField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'call_id',
        'name',
        'label',
        'type',
        'required',
        'options_json',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'required'     => 'boolean',
        'options_json' => 'array',
        'order'        => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // null = platné pre celý program; not null = špecifické pre danú výzvu
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class, 'field_id');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isProgramWide(): bool
    {
        return $this->call_id === null;
    }
}
