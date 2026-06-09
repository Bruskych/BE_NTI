<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель специализации для связки с конкурсными отборами, задачами и командами */
class Specialization extends Model
{
    use SoftDeletes, HasFactory;

    // Допустимые значения stack (квалификационные стеки Программы А)
    const STACKS = ['01', '02', '03', '04', '05'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'stack',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class, 'call_specialization');
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class, 'challenge_specialization');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_specialization');
    }
}
