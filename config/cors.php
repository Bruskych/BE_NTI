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
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', 'logout'],

    // Разрешённые HTTP-методы (звёздочка = все методы)
    'allowed_methods' => ['*'],

    // Разрешённые источники запросов (домены фронтенда)
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],

    // Паттерны разрешённых источников (регулярные выражения)
    'allowed_origins_patterns' => [],

    // Разрешённые заголовки запросов (звёздочка = все)
    'allowed_headers' => ['*'],

    // Заголовки, которые браузер может прочитать из ответа
    'exposed_headers' => [],

    // Время кэширования результата preflight-запроса (в секундах; 0 = не кэшировать)
    'max_age' => 0,

    // Разрешить передачу cookie и учётных данных в кросс-доменных запросах
    'supports_credentials' => true,

];
