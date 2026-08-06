<?php

use App\Models\Question;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('conversationChat.{roomId}',function($user,$roomId){
//     return[
//         'id'=>$user->id,
//         'name'=>$user->name
//     ];
// });

Broadcast::channel('onlineUser',function ($user){
    return[
        'id'=>$user->id,
        'name'=>$user->name
    ];
}); 

Broadcast::channel('questionLike.{id}',function($user,$id){
    return true;
});

Broadcast::channel('questionComment.{id}',function($user,$id){
    return true;
});