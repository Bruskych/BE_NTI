<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialization extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
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
