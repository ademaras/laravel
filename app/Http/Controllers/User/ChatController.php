<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ServicesChat;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $serviceChat;

    public function __construct()
    {
        $serviceChat = new ServicesChat;
        $this->serviceChat = $serviceChat;
    }

    public function newContent($id)
    {
        $chatData = app(ServicesChat::class)->showChat($id);

        return json_encode($chatData);
    }

    public function index()
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();
        $chats = $this->serviceChat->chatList();
        return view('user.chat.index', compact('users', 'chats'));
    }

    public function store(Request $request)
    {
        $receiver_id =  $request->input('users', [])[0];
        $senderId = Auth::user()->id;
        $receiverId = $receiver_id;

        $timestamp = time();

        $this->serviceChat->create($senderId, $receiverId, $timestamp);
        return redirect()->back()->with('success', 'Sohbet Oluşturuldu');
    }

    public function sendMessage(Request $request)
    {
        $receiverId =  $request->receiver_id;
        $senderId = Auth::user()->id;

        if ($request->hasFile('file')) {
            $filename = $request->file('file');
            $imagePath = $filename->store('chat_files', 'public');
            $url = 'storage/' . $imagePath;
        }
        $timestamp = time();
        $newMessage = [
            "isLiked" => "0",
            "media" => $url ?? '',
            "mediaType" => "story",
            "message" => $request->message,
            "sender" => $senderId,
            "visibility" => "all",
            "timestamp" => $timestamp
        ];

        $this->serviceChat->sendMessage($senderId, $receiverId, $timestamp, $newMessage);

        return back();
    }

    public function chat($id)
    {
        $chats = $this->serviceChat->chatList();
        $chatShows = $this->serviceChat->showChat($id);
        $users = User::where('id', '!=', Auth::user()->id)->get();
        $receiver_id = $id;

        return view('user.chat.chat', compact('chatShows', 'chats', 'users', 'receiver_id'));
    }

    public function getChat($id)
    {
        $chatShows = $this->serviceChat->showChat($id);
        return response()->json(['chatShows' => $chatShows]);
    }
}
