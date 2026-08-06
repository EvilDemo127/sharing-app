<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LikeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $user,public $question)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

     /**
     * To save notifications table
     */
    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

     /**
     * To save Reverb broadcast 
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            $this->toArray($notifiable)
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return[
            'type'=>'like',
            'actor_id' => $this->user->id,
            'actor_name' => $this->user->name,
            'target_type' => 'question',
            'target_id' => $this->question->id,
            'message' => $this->user->name . ' liked your question.',
            'url' => route(
                'question.details',
                $this->question->slug
            ),
        ];
    }
}
