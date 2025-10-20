<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function store(Request $req, int $conversationId): RedirectResponse
    {
        $user = $req->user();
        $conv = Conversation::findOrFail($conversationId);

        // 参加者だけ送信可
        abort_unless($conv->users()->where('user_id',$user->id)->exists(), 403);

        $msg = Message::create([
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'body' => $req->validate(['body'=>'required|string|max:2000'])['body'],
        ]);

        $conv->touch(); // 並び替え用

        broadcast(new MessageCreated($msg))->toOthers();

        return back(303);
    }
}
