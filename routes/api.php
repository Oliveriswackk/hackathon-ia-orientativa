<?php

use App\Http\Controllers\AIController;
use App\Services\AI\AIManager;
use App\Services\AI\LlamaProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/ai/status', function (Request $request, AIManager $aiManager, LlamaProvider $llamaProvider) {
    if ($request->boolean('refresh')) {
        Cache::forget('ai.available.llama');
        Cache::forget('ai.available.ollama');
    }

    $llamaAvailable = $aiManager->isLlamaAvailable();
    $ollamaAvailable = $aiManager->isOllamaAvailable();

    $payload = [
        'active_provider' => $aiManager->activeProvider(), // llama|ollama
        'llama_available' => $llamaAvailable,
        'ollama_available' => $ollamaAvailable,
        'mode' => $llamaAvailable ? 'online' : 'offline',
    ];

    if (config('app.debug') && $request->boolean('diagnose')) {
        $payload['groq_diagnose'] = $llamaProvider->diagnoseGroq();
    }

    return response()->json($payload);
});

Route::post('/ai/ask', [AIController::class, 'ask']);
Route::post('/ai/ask-context', [AIController::class, 'askWithContext']);

