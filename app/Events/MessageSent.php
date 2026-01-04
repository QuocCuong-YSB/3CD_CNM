<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender:id,name,email', 'receiver:id,name,email']);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('chat.' . $this->message->receiver_id);
        
        return $channel;
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }

    public function broadcastWith()
    {
        $data = [
            'message' => [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at->toISOString(),
                'sender' => $this->message->sender,
                'receiver' => $this->message->receiver,
            ]
        ];
        
        return $data;
    }
}