<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Запуск секундомера (Сколько секунд ушло на конкретный запрос)
define('LARAVEL_START', microtime(true));

// Если сайт закрыт (есть файл maintenance.php), запрос будет отклонён а сайт покажет "Сайт на обслуживании"
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Подключение autoload.php чтобы Laravel понимал местонахождение всех файлов (Контроллеры, Модели...)
require __DIR__.'/../vendor/autoload.php';

// Подключение bootstrap/app.php
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// capture() берёт инф. HTTP-запроса, упаковывает в Request обьект, передаёт в routes/api, ищет нужный Controller
$app->handleRequest(Request::capture());
