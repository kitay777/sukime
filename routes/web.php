<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Middleware\EnsureProfileCompleted;



use App\Http\Controllers\QuestionController; // ←ユーザー用
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController; // ←管理用

use App\Http\Controllers\GachaController;
use App\Http\Controllers\CrushController;
use App\Http\Controllers\OshiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


// routes/web.php の先頭あたりに一時追加（テスト後に削除）
Route::post('/stripe/ping', function () {
    @file_put_contents(storage_path('logs/stripe_ping.log'), now()." PING\n", FILE_APPEND);
    return response('pong', 200);
})->withoutMiddleware([
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
]);


Route::middleware(['auth','verified'])->group(function () {
    Route::get('/points', [PointController::class, 'dashboard'])->name('points.dashboard');
    Route::post('/points/checkout', [PointController::class, 'createCheckoutSession'])->name('points.checkout');
    Route::get('/points/success', [PointController::class, 'success'])->name('points.success');
    Route::get('/points/cancel',  [PointController::class, 'cancel'])->name('points.cancel');
});

// Webhook（認証なし・CSRF除外）
Route::post('/stripe/webhook', \App\Http\Controllers\StripeWebhookController::class)
    ->name('stripe.webhook');

Route::middleware(['auth','verified'])->group(function () {
    // 会話一覧（任意）
    Route::get('/chat', [ConversationController::class, 'index'])->name('chat.index');

    // 会話ルームを開く（これをダッシュボードから使う）
    Route::get('/chat/{conversation}', [ConversationController::class, 'show'])
        ->name('chat.room');

    // メッセージ送信
    Route::post('/chat/{conversation}/messages', [MessageController::class, 'store'])
        ->name('messages.store');
});

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/chat', [ConversationController::class, 'index'])->name('chat.index');
    Route::post('/chat/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // 既存: トップやガチャなど
    // Route::get('/', [ProductController::class, 'publicView'])->name('products.all');
    // Route::get('/gacha/play', [GachaController::class, 'play'])->name('gacha.play');

    // ダッシュボード（単一アクションコントローラ）
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // 好きな人（週1制限を controller で判定）
    Route::post('/crush', [CrushController::class, 'store'])->name('crush.store');

    // 推し
    Route::post('/oshi', [OshiController::class, 'store'])->name('oshi.store');
    Route::delete('/oshi/{id}', [OshiController::class, 'destroy'])->name('oshi.destroy');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/gacha/play', [GachaController::class, 'play'])->name('gacha.play');
});

// ユーザー用
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/questions/initial', [QuestionController::class, 'initial'])->name('questions.initial');
    Route::get('/questions/daily', [QuestionController::class, 'daily'])->name('questions.daily');
    Route::post('/questions/store', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/flow', [QuestionController::class, 'showQuestionFlow'])->name('questions.flow');
});

// 管理者用
Route::middleware(['auth', 'verified', 'can:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('questions', AdminQuestionController::class);
    });

Route::middleware(['auth', 'verified', 'can:admin']) // admin権限のみに制限
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('questions', AdminQuestionController::class);
    });


Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {
    Route::get('/favorite', [FavoriteController::class, 'create'])->name('favorite.create');
    Route::post('/favorite', [FavoriteController::class, 'store'])->name('favorite.store');
});

/*
Route::middleware(['auth', 'verified', 'profile.completed', 'favorite.selected'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified', 'questions.flow'])->name('dashboard');
});
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])
        ->name('onboarding.create');
    Route::post('/onboarding', [OnboardingController::class, 'store'])
        ->name('onboarding.store');
});
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

