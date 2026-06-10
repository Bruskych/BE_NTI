<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Пути, для которых применяются правила CORS
    'paths' => ['*'],

    // Разрешённые HTTP-методы (звёздочка = все методы)
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', '*'],

    // Разрешённые источники запросов (домены фронтенда)
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000', 'http://localhost:3001', 'http://127.0.0.1:3001', 'http://localhost:5173', 'http://127.0.0.1:5173'],

    // Паттерны разрешённых источников (регулярные выражения) — любой порт на localhost для dev-серверов
    'allowed_origins_patterns' => ['#^http://(localhost|127\.0\.0\.1):\d+$#'],

    // Разрешённые заголовки запросов (звёздочка = все)
    'allowed_headers' => ['*'],

    // Заголовки, которые браузер может прочитать из ответа
    'exposed_headers' => [],

    // Время кэширования результата preflight-запроса (в секундах; 0 = не кэшировать)
    'max_age' => 0,

    // Разрешить передачу cookie и учётных данных в кросс-доменных запросах
    'supports_credentials' => true,

];
