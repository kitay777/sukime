<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // /broadcasting/auth の認可ルートを登録（web ミドルウェア）
        Broadcast::routes();

        // チャンネル認可定義を読み込み
        require base_path('routes/channels.php');
    }
}
