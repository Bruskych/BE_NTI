<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель email-шаблона с подстановкой переменных */
class EmailTemplate extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables_json',
    ];

    protected $casts = [
        'variables_json' => 'array',
    ];

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    // Nahradí premenné v šablóne skutočnými hodnotami
    // napr. ['name' => 'Ján'] → "Dobrý deň, Ján"
    public function render(array $variables = []): string
    {
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $body = str_replace('{{ ' . $key . ' }}', $value, $body);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }
}
