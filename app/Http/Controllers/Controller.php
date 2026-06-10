<?php

namespace App\Http\Controllers;

use App\Http\Concerns\HasApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'NTI API',
    description: 'REST API pre platformu Národná technologická iniciatíva (NTI) — registrácie, prihlášky, hodnotenie, projekty, dokumenty, CMS a administráciu.'
)]
#[OA\Server(
    url: '/api',
    description: 'API server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    description: 'Sanctum personal access token (Authorization: Bearer {token})'
)]
/** Базовый контроллер приложения с поддержкой авторизации, валидации и API-ответов */
abstract class Controller
{
    use AuthorizesRequests, HasApiResponse, ValidatesRequests;

    /**
     * Build the standard resource policy "can:" middleware (viewAny/view/create/update/delete)
     * for the given model, mapped to controller methods. Replaces authorizeResource(), which
     * relies on constructor-registered middleware no longer applied to routes since Laravel 11.
     *
     * @return array<int, Middleware>
     */
    protected static function resourcePolicyMiddleware(string $model, ?string $parameter = null): array
    {
        $parameter = $parameter ?: Str::snake(class_basename($model));

        $abilityMap = [
            'index'   => 'viewAny',
            'show'    => 'view',
            'create'  => 'create',
            'store'   => 'create',
            'edit'    => 'update',
            'update'  => 'update',
            'destroy' => 'delete',
        ];

        $methodsWithoutModel = ['index', 'create', 'store'];

        $methodsByAbilityName = [];
        foreach ($abilityMap as $method => $ability) {
            $modelName = in_array($method, $methodsWithoutModel, true) ? $model : $parameter;
            $methodsByAbilityName["can:{$ability},{$modelName}"][] = $method;
        }

        return array_map(
            fn (string $name, array $methods) => (new Middleware($name))->only($methods),
            array_keys($methodsByAbilityName),
            array_values($methodsByAbilityName),
        );
    }
}
