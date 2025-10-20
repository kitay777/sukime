<?php
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return \App\Models\Conversation::find($conversationId)?->users()->where('user_id',$user->id)->exists() ?: false;
});
