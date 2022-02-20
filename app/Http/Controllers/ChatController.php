<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chats');
    }

    public function getMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        Log::info($request);
        $user = Auth::user();

        $message = $user->messages()->create([
            'message' => $request->message
        ]);

        broadcast(new SendMessage($user, $message))->toOthers();
        
        return 'message sent';
    }
}
