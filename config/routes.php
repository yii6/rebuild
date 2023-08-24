<?php

declare(strict_types=1);

use App\Controller\HelloController;
use App\Middleware\MiddlewareB;

return [
    ['GET', '/hello/index', [HelloController::class, 'index'], [
        'middlewares' => [
            MiddlewareB::class,
        ],
    ]],
    ['GET', '/hello/hyperf', [HelloController::class, 'hyperf']],
];
