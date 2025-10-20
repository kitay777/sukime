<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\EnsureFavoriteSelected;
use App\Http\Middleware\EnsureQuestionsFlow;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1) ミドルウェアのエイリアス
        $middleware->alias([
            'profile.completed' => EnsureProfileCompleted::class,
            'favorite.selected' => EnsureFavoriteSelected::class,
            'questions.flow'    => EnsureQuestionsFlow::class,
        ]);

        // 2) 逆プロキシ
        $middleware->trustProxies(
            at: '*',
            headers:
                Request::HEADER_X_FORWARDED_FOR  |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO|
                Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // 3) webグループに Inertia 関連を追加
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // 4) CSRF 除外（← これが Laravel 12 のやり方）
        $middleware->validateCsrfTokens(except: [
            '/stripe/webhook',
        ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\BroadcastServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
