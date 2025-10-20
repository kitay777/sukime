<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $convs = Conversation::whereHas('users', fn($q)=>$q->where('user_id',$user->id))
            ->with(['users:id,name'])
            ->latest('updated_at')
            ->get();

        return Inertia::render('Chat/Index', [
            'conversations' => $convs->map(fn($c)=>[
                'id'      => $c->id,
                'members' => $c->users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name]),
            ]),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $user = $request->user();

        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        // メッセージ（これはOK）
        $messages = $conversation->messages()
            ->with('user:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        // ★ ここを完全修飾に
        $members = $conversation->users()
            ->select('users.id', 'users.name')   // ← 重要
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('Chat/Room', [
            'conversation' => [
                'id'      => $conversation->id,
                'members' => $members,
            ],
            'messages' => $messages->map(fn($m) => [
                'id'         => $m->id,
                'user_id'    => $m->user_id,
                'user_name'  => $m->user->name,
                'body'       => $m->body,
                'created_at' => $m->created_at->toDateTimeString(),
            ]),
        ]);
    }

}
