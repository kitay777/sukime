<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use App\Models\TweetMedia;
use App\Models\TweetUnlock;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Support\CrushMatcher;
use App\Models\User;
use App\Models\Oshi;


class TweetController extends Controller
{
    /** タイムライン（ロック情報付き） */
 public function index(Request $req)
{
    $user    = $req->user();
    $oshiId  = $req->input('oshi_id'); // ★ 推しごと一覧
    $query   = Tweet::with('user:id,name', 'media:id,tweet_id,kind,thumb_path,path,sort_order')->latest();

    $targetUser = null;

    if ($oshiId) {
        // ログインユーザーの Oshi から権限ガード
        $oshi = Oshi::where('user_id', $user->id)->find($oshiId);
        if ($oshi) {
            // 自分以外でプロフィール一致するユーザーを候補に
            $candidates = User::query()
                ->where('id', '!=', $user->id)
                ->get(['id','name','school','faculty','grade','gender'])
                ->filter(fn(User $u) => CrushMatcher::crushEqualsProfile($oshi, $u));

            if ($candidates->count()) {
                $targetUser = $candidates->random();
                $query->where('user_id', $targetUser->id);
            } else {
                // 候補ゼロの場合は空にするために存在しないIDで絞る
                $query->where('user_id', 0);
            }
        } else {
            // 自分の Oshi でなければ空
            $query->where('user_id', 0);
        }
    }

    $tweets = $query->paginate(20);

    // シリアライズ：解放フラグと URL を付与。価格は「推し一覧モードなら100pt固定」、通常はprice_points
    $mapped = $tweets->through(function (Tweet $t) use ($user, $targetUser) {
        $unlocked = $t->isUnlockedFor($user?->id);

        $media = $t->media->map(function (TweetMedia $m) use ($unlocked) {
            $thumbUrl = asset("storage/{$m->thumb_path}");
            $signed   = $unlocked
                ? URL::temporarySignedRoute('tweet.media.stream', now()->addMinutes(5), ['path' => $m->path])
                : null;

            return [
                'id'   => $m->id,
                'kind' => $m->kind,
                'thumb'=> $thumbUrl,
                'url'  => $signed,
                'sort' => $m->sort_order,
            ];
        });

        // ★ 推し一覧モードなら 100pt 固定 / それ以外はツイート価格
        $inOshiMode  = (bool) $targetUser;
        $unlockPrice = $inOshiMode ? 100 : ($t->price_points ?? 0);

        return [
            'id'             => $t->id,
            'user'           => ['id'=>$t->user->id, 'name'=>$t->user->name],
            'title'          => $t->title,
            'body'           => $unlocked ? $t->body : null,
            'excerpt'        => $unlocked ? null : mb_strimwidth($t->body, 0, 100, '…'),
            'is_paid'        => $t->is_paid || $inOshiMode, // 推しモード中は強制ロック扱い
            'price_points'   => $t->price_points,
            'unlock_price'   => $unlockPrice, // ← フロントはこっちを表示
            'viewerUnlocked' => $unlocked,
            'media'          => $media,
            'created_at'     => $t->created_at->toDateTimeString(),
        ];
    });

return Inertia::render('Tweets/Index', [
    'tweets'     => $mapped,
    'oshi_mode'  => (bool) $targetUser,
    'oshi_user'  => $targetUser ? ['id'=>$targetUser->id, 'name'=>$targetUser->name] : null,
    // ★ フォーム表示可否：推しモードで “自分≠対象ユーザー” のときは false
    'can_post'   => !$targetUser || ($targetUser && $targetUser->id === $user->id),
]);
}

    /** 詳細 */
    public function show(Request $req, Tweet $tweet)
    {
        $user = $req->user();
        $unlocked = $tweet->isUnlockedFor($user?->id);

        $tweet->load('user:id,name', 'media:id,tweet_id,kind,thumb_path,path,sort_order');

        $media = $tweet->media->map(function (TweetMedia $m) use ($unlocked) {
            $thumbUrl = asset("storage/{$m->thumb_path}");
            $signed = null;
            if ($unlocked) {
                $signed = URL::temporarySignedRoute(
                    'tweet.media.stream',
                    now()->addMinutes(5),
                    ['path' => $m->path]
                );
            }
            return [
                'id'   => $m->id,
                'kind' => $m->kind,
                'thumb'=> $thumbUrl,
                'url'  => $signed,
                'sort' => $m->sort_order,
            ];
        });

        return Inertia::render('Tweets/Show', [
            'tweet' => [
                'id'             => $tweet->id,
                'user'           => ['id'=>$tweet->user->id, 'name'=>$tweet->user->name],
                'title'          => $tweet->title,
                'body'           => $unlocked ? $tweet->body : null,
                'is_paid'        => $tweet->is_paid,
                'price_points'   => $tweet->price_points,
                'media'          => $media,
                'viewerUnlocked' => $unlocked,
                'created_at'     => $tweet->created_at->toDateTimeString(),
            ],
        ]);
    }

    /** 投稿（画像/動画アップロード） */
    public function store(Request $req)
    {
        $user = $req->user();

        $data = $req->validate([
            'title'         => ['nullable','string','max:120'],
            'body'          => ['required','string','max:8000'],
            'is_paid'       => ['required','boolean'],
            'price_points'  => ['nullable','integer','min:1','max:100000'],
            'media'         => ['nullable','array','max:10'],
            'media.*.file'  => ['required','file','mimes:jpg,jpeg,png,webp,mp4,mov,webm','max:51200'], // 50MB
            'media.*.kind'  => ['required','in:image,video'],
            'media.*.sort'  => ['nullable','integer','min:0','max:1000'],
        ]);

        if (!$data['is_paid']) {
            $data['price_points'] = null;
        }

        DB::transaction(function () use ($user, $data) {
            $tweet = Tweet::create([
                'user_id'      => $user->id,
                'title'        => $data['title'] ?? null,
                'body'         => $data['body'],
                'is_paid'      => (bool) $data['is_paid'],
                'price_points' => $data['price_points'] ?? null,
            ]);

            $mediaItems = collect($data['media'] ?? []);
            $sortBase   = 0;

            foreach ($mediaItems as $item) {
                $file = $item['file'];
                $kind = $item['kind'];

                // ファイル名をランダム化
                $basename = Str::random(24) . '.' . strtolower($file->getClientOriginalExtension());

                // original: private ディスク
                $origPath = $file->storeAs("tweets/{$tweet->id}/orig", $basename, 'private');

                // thumb: public ディスク（まずは原寸コピー。後でリサイズに差し替え推奨）
                Storage::disk('public')->putFileAs("tweets/{$tweet->id}/thumb", $file, $basename);
                $thumbPath = "tweets/{$tweet->id}/thumb/{$basename}";

                TweetMedia::create([
                    'tweet_id'   => $tweet->id,
                    'kind'       => $kind,
                    'path'       => $origPath,   // private
                    'thumb_path' => $thumbPath,  // public
                    'sort_order' => $item['sort'] ?? $sortBase++,
                ]);
            }
        });

        return back()->with('success', 'ツイートを投稿しました。');
    }

    /** アンロック（購入）: 50% をクリエイターに還元 */
 public function unlock(Request $req, Tweet $tweet)
{
    $buyer = $req->user();

    if ($tweet->user_id === $buyer->id) {
        return back()->with('info','自分のツイートは購入不要です。');
    }

    // ★ 推しモードなら固定100pt、通常はツイートに設定の価格
    $requestedPrice = (int) $req->input('price_points', 0);
    $price = $requestedPrice > 0 ? $requestedPrice : (int) ($tweet->price_points ?? 0);
    if ($price <= 0) {
        return back()->with('error','価格が不正です。');
    }

    // 既に購入済み？
    if (TweetUnlock::where('tweet_id',$tweet->id)->where('buyer_id',$buyer->id)->exists()) {
        return back()->with('success','すでにアンロック済みです。');
    }

    $balance = (int) PointTransaction::where('user_id',$buyer->id)->sum('amount');
    if ($balance < $price) {
        return redirect()->route('points.dashboard')->with('error','ポイントが不足しています。');
    }

    DB::transaction(function () use ($tweet, $buyer, $price) {
        // 1) 購入者から差し引き
        PointTransaction::create([
            'user_id' => $buyer->id,
            'amount'  => -$price,
            'type'    => 'spend',
            'reason'  => 'tweet_unlock',
        ]);

        // 2) クリエイターへ 50% 付与
        $creatorShare = (int) floor($price * 0.5);
        if ($creatorShare > 0) {
            PointTransaction::create([
                'user_id' => $tweet->user_id,
                'amount'  => $creatorShare,
                'type'    => 'earning',
                'reason'  => 'tweet_revenue',
            ]);
        }

        // 3) アンロック記録
        TweetUnlock::create([
            'tweet_id'     => $tweet->id,
            'buyer_id'     => $buyer->id,
            'price_points' => $price,
        ]);
    });

    return back()->with('success','アンロックしました。');
}


    /** private ディスクのファイルを署名URLで配信（localでも可） */
    public function stream(Request $req)
    {
        abort_unless($req->hasValidSignature(), 403);

        $path = $req->query('path');
        $disk = Storage::disk('private');

        abort_unless($disk->exists($path), 404);

        // 簡易MIME判定。必要に応じて拡張
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->response($path, null, ['Content-Type' => $mime]);
    }
}
