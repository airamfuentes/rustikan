<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ChatIAController extends Controller
{
    public function send(Request $request, GeminiService $gemini): JsonResponse
    {
        $data = $request->validate([
            'message'           => ['required', 'string', 'min:1', 'max:1000'],
            'history'           => ['nullable', 'array', 'max:20'],
            'history.*.role'    => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.text'    => ['required_with:history', 'string', 'max:2000'],
        ]);

        // Rate limit: por IP + usuario, 20 mensajes / minuto
        $key = 'chat-ia:' . ($request->user()?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'reply' => "Has enviado demasiados mensajes seguidos. Espera {$seconds}s e inténtalo de nuevo.",
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $reply = $gemini->chat($data['message'], $data['history'] ?? []);

        return response()->json(['reply' => $reply]);
    }
}
