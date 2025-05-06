<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Muestra la vista del chat.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * Almacena un nuevo mensaje y lo transmite.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        $message = $user->messages()->create([
            'content' => $request->input('message'),
        ]);

        broadcast(new MessageSent($user, $message))->toOthers();

        return ['status' => 'success'];
    }

    /**
     * Obtiene los mensajes recientes.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchMessages()
    {
        return Message::with('user')->latest()->take(50)->get();
    }
}