<?php

use App\Http\Controllers\AIController;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Route;

Route::get('/ai/status', function (AIManager $aiManager) {
    $llamaAvailable = $aiManager->isLlamaAvailable();
    $ollamaAvailable = $aiManager->isOllamaAvailable();

    return response()->json([
        'active_provider' => $aiManager->activeProvider(), // llama|ollama
        'llama_available' => $llamaAvailable,
        'ollama_available' => $ollamaAvailable,
        'mode' => $llamaAvailable ? 'online' : 'offline',
    ]);
});

Route::post('/ai/ask', [AIController::class, 'ask']);
Route::post('/ai/ask-context', [AIController::class, 'askWithContext']);

