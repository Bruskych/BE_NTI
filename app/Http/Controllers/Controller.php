<?php

namespace App\Http\Controllers;

use App\Http\Concerns\HasApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
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
abstract class Controller extends BaseController
{
    use AuthorizesRequests, HasApiResponse, ValidatesRequests;
}
