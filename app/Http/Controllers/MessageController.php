<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function message()
    {
        $authId = Auth::id();
        $users =$this->unread_count($authId);
        return Inertia::render('Message', ['users' => $users]);
    }

    public function get_message($uuid)
    {
        $authId = Auth::id();
        $search = $uuid ? User::where('uuid', $uuid)->firstOrFail() : null;
        $searchId = $search ? $search->id : null;

        $messages = Message::with('sender', 'receiver')->where(function ($q) use ($authId, $searchId) {
            $q->where('sender_id', $authId)->where('receiver_id', $searchId);
        })->orWhere(function ($q) use ($authId, $searchId) {
            $q->where('sender_id', $searchId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        $users =$this->unread_count($authId);
        return Inertia::render('Message', [
            'users' => $users,
            'messages' => $messages,
            'selectedUser' => $search ? $search->uuid : null
        ]);
    }

    public function store_message(Request $request)
    {
        $valiMessage = $request->validate([
            'receiver_id' => ['required', 'exists:users,id','different:'.Auth::id()],
            'message' => ['required', 'string', 'max:200'],
        ]);
        $valiMessage['sender_id'] = Auth::id();
        $message = Message::create($valiMessage);
        $message->load('sender');

        try {
            broadcast(new MessageSent($message))->toOthers();
            Log::info('MessageSent broadcast fired', [
            'id' => $message->id,
            'receiver' => $message->receiver_id,
            'channel' => 'chat.' . $message->receiver_id
        ]);
        } catch (\Throwable $e) {
            Log::warning('Message broadcast failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function read_message(Message $message)
    {
        if($message->receiver_id !==Auth::id())
            {
                abort(403);
            }
        $message->is_read = true;
        $message->save();

        try {
            broadcast(new MessageRead($message));
            logger('Message broadcast read');
        } catch (\Throwable $e) {
            Log::warning('Message read broadcast failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function offline()
    {
        $user =Auth::user();
        $user->update([
            "last_seen"=>now()
        ]);
        
       
        return response()->json([
            'success'=>true
        ]);
    }

    public function unread_count($authId)
    {
        return User::whereNot('id',$authId)->withCount(['sendMessages as unread_count'=>function($q) use($authId){
            $q->where('receiver_id',$authId)->where('is_read',false);
        }])->get();
    }

    public function get_noti()
    {
        $user =Auth::user();
        $noti =$user->notifications;
        return response()->json(['noti'=>$noti]);
    }

    public function read_noti($id)
    {
        $user =Auth::user();
        $user->notifications()->where('id',$id)->update([
            'read_at'=>now()
        ]);
        return response()->json(['success'=>true]);
    }
}
