<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassChatController extends Controller
{
    /**
     * GET /api/v1/classes/{class}/chats
     * নির্দিষ্ট class-এর chat মেসেজগুলো আনা
     */
    public function index(Request $request, $classId)
    {
        $user = Auth::user(); // লাগলে membership check করতে পারো

        $limit = (int) $request->query('limit', 50);
        if ($limit < 1)  $limit = 1;
        if ($limit > 100) $limit = 100;

        $messages = Message::with(['sender:id,name,email'])
            ->where('message_scope', 'class')
            ->where('class_id', $classId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(function (Message $m) {
                return [
                    'id'         => $m->id,
                    'class_id'   => $m->class_id,
                    'sender_id'  => $m->sender_id,
                    'sender'     => $m->sender ? [
                        'id'    => $m->sender->id,
                        'name'  => $m->sender->name,
                        'email' => $m->sender->email,
                    ] : null,
                    'content'    => $m->content,
                    'message_type' => $m->message_type,
                    'file_path'  => $m->file_path,
                    'created_at' => optional($m->created_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'message' => 'Class chats fetched successfully.',
            'data'    => $messages,
        ]);
    }

    /**
     * POST /api/v1/classes/{class}/chats
     * নতুন chat মেসেজ পাঠানো
     *
     * Body:
     * {
     *   "message": "Sir, assignment deadline extend hobe?"
     * }
     */
    public function store(Request $request, $classId)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $msg = Message::create([
            'sender_id'     => $user->id,
            'receiver_id'   => null,
            'class_id'      => $classId,
            'message_scope' => 'class',   // ENUM('private','class','system') অনুযায়ী
            'content'       => $validated['message'],
            'message_type'  => 'Text',
            'file_path'     => null,
            'is_read'       => false,
        ]);

        $msg->load('sender:id,name,email');

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => [
                'id'         => $msg->id,
                'class_id'   => $msg->class_id,
                'sender_id'  => $msg->sender_id,
                'sender'     => $msg->sender ? [
                    'id'    => $msg->sender->id,
                    'name'  => $msg->sender->name,
                    'email' => $msg->sender->email,
                ] : null,
                'content'    => $msg->content,
                'message_type' => $msg->message_type,
                'file_path'  => $msg->file_path,
                'created_at' => optional($msg->created_at)->toDateTimeString(),
            ],
        ], 201);
    }
}