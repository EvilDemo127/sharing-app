<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\QuestionComment as QuestionCommentModel;

class QuestionComment implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $comment,$comment_count,$action;
    public function __construct($comment,$comment_count,$action)
    {
        $this->comment = $comment;
        $this->comment_count = $comment_count;
        $this->action=$action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn()
    {
        return
            new PrivateChannel('questionComment.' . $this->comment->question_id);
    }

    public function broadcastWith()
    {
        return [
            'action' => $this->action,
            'comment' => $this->comment,
            'comment_count'=>$this->comment_count,
            'user'=>$this->comment->user
        ];
    }

    public function broadcastAs()
    {
        return 'QuestionComment';
    }
}
