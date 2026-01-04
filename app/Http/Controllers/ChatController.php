<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000'
        ]);
        $user = auth()->user();
        if ($user->id == $request->receiver_id) {
            return response()->json(['error' => 'Không thể gửi tin nhắn cho chính mình'], 400);
        }
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'message' => trim($request->message)
        ]);
        $message->load(['sender:id,name,email', 'receiver:id,name,email']);
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 201);
        }

        return response()->json($message, 201);
    }

    public function history($userId)
    {
        $currentUserId = auth()->id();

        if (!User::find($userId)) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }

        $messages = Message::where(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $currentUserId)
              ->where('receiver_id', $userId);
        })
        ->orWhere(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $currentUserId);
        })
        ->with(['sender:id,name,email', 'receiver:id,name,email'])
        ->orderBy('created_at', 'asc')
        ->get();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function conversations()
    {
        $userId = auth()->id();

        $conversations = DB::table('messages')
            ->select(
                DB::raw('CASE 
                    WHEN sender_id = ' . $userId . ' THEN receiver_id 
                    ELSE sender_id 
                END as user_id'),
                DB::raw('MAX(created_at) as last_message_time')
            )
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->groupBy('user_id')
            ->orderBy('last_message_time', 'desc')
            ->get();

        $userIds = $conversations->pluck('user_id');

        $users = User::whereIn('id', $userIds)
            ->select('id', 'name', 'email', 'level')
            ->get()
            ->map(function ($user) use ($userId) {
                $lastMessage = Message::where(function ($q) use ($userId, $user) {
                    $q->where('sender_id', $userId)->where('receiver_id', $user->id);
                })
                ->orWhere(function ($q) use ($userId, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->first();

                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                return [
                    'user' => $user,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount
                ];
            });

        return response()->json($users);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id'
        ]);

        $updated = Message::where('sender_id', $request->sender_id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Đã đánh dấu đọc',
            'updated_count' => $updated
        ]);
    }

    public function unreadCount()
    {
        $count = Message::where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function historyUser(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|integer',
            'admin_id' => 'required|integer',
        ]);

        $messages = Message::where(function($q) use ($request) {
                $q->where('sender_id', $request->user_id)
                  ->orWhere('receiver_id', $request->user_id);
            })
            ->where(function($q) use ($request) {
                $q->where('sender_id', $request->admin_id)
                  ->orWhere('receiver_id', $request->admin_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}