<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Events\ChatBot;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    public function chatBot()
    {
        event(new ChatBot('hola'));
        return response('OK', 200);
    }

    public function chatBotData(Request $request)
    {
        event(new ChatBot($request->mensaje));
        return response($request['hub.challenge'], 200)->withHeaders([
            'Content-Type' => 'text/plain; charset=utf-8',
            'X-Powered-By' => 'Express',
            'x-powered-by' => 'Express',
        ]);
    }
}
