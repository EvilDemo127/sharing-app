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

class QuestionLike implements ShouldBroadcastNow
{
    
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public function __construct(public $question)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn() 
    {
        return new PrivateChannel('questionLike.'.$this->question->id);
    }

    public function broadcastWith()
    {
        return [
            'question_id'=>$this->question->id,
            'like_count'=>$this->question->like()->count(),
        ];
    }

    public function broadcastAs()
    {
        return 'QuestionLike';
    }
}
