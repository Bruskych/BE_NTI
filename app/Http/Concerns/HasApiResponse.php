<?php

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Типизированная обёртка вокруг response()->json() с флагами JSON_UNESCAPED_UNICODE
 * и JSON_UNESCAPED_SLASHES. Используется базовым контроллером вместо прямого вызова
 * response()->api() (макроса), чтобы IDE понимал возвращаемый тип.
 */
trait HasApiResponse
{
    /**
     * Вернуть JSON-ответ API.
     *
     * @param  mixed  $data     Данные ответа (массив, Resource, ResourceCollection, null и т.д.)
     * @param  int    $status   HTTP-статус (по умолчанию 200)
     * @param  array  $headers  Дополнительные заголовки
     */
    protected function apiJson(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
