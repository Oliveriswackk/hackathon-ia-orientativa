<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Services\GroqService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlamaProvider implements AIProviderInterface
{
    protected function groqModel(): string
    {
        return (string) config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    public function ask(string $prompt, string $systemPrompt = ''): string
    {
        $messages = [];

        if (!empty($systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        // Reutilizamos GroqService existente (no se toca su lógica).
        $groq = new GroqService();
        return $groq->chat($messages);
    }

    public function askWithContext(array $context, string $question): string
    {
        // Caso: contexto normativo (usado por TraductorController con chatConNormativa).
        if (!empty($context['system_prompt']) && array_key_exists('documento_normativo', $context)) {
            $systemPrompt = (string) $context['system_prompt'];
            $documentoNormativo = $context['documento_normativo'];

            $groq = new GroqService();
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $question],
            ];

            return $groq->chatConNormativa($messages, $documentoNormativo);
        }

        // Mismo formato que el OllamaService actual, para mantener compatibilidad
        // con el shape que el frontend ya envía en `context`.
        $systemPrompt = '';

        if (!empty($context['project'])) {
            $systemPrompt .= "Proyecto: {$context['project']}\n\n";
        } else {
            $systemPrompt .= "Proyecto: (desconocido)\n\n";
        }

        if (!empty($context['files']) && is_array($context['files'])) {
            foreach ($context['files'] as $file) {
                $name = $file['name'] ?? 'archivo';
                $description = $file['description'] ?? '';
                $content = $file['content'] ?? '';

                $systemPrompt .= "--- Archivo: {$name} ---\n";

                if (!empty($description)) {
                    $systemPrompt .= "Descripción: {$description}\n";
                }

                $systemPrompt .= "{$content}\n\n";
            }
        } else {
            // Fallback razonable: inyectar el contexto como JSON.
            $systemPrompt .= "Contexto JSON:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
        }

        $systemPrompt .= "Usa el contexto anterior para responder con precisión.";

        return $this->ask($question, $systemPrompt);
    }

    public function isAvailable(): bool
    {
        $apiKey = config('services.groq.key');
        if ($apiKey === '') {
            return false;
        }

        try {
            // Request ligero para comprobar conectividad real.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
                ->timeout(12)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $this->groqModel(),
                    'messages' => [
                        ['role' => 'user', 'content' => 'ping'],
                    ],
                    'max_tokens' => 1,
                ]);

            $json = $response->json();

            if (!$response->successful()) {
                Log::warning('Groq availability check failed', [
                    'http_status' => $response->status(),
                    'groq_error' => $json['error']['message'] ?? ($response->body() !== '' ? substr($response->body(), 0, 500) : null),
                    'model' => $this->groqModel(),
                ]);

                return false;
            }

            return isset($json['choices'][0]['message']['content']);
        } catch (\Throwable $e) {
            Log::warning('Groq availability check exception', [
                'message' => $e->getMessage(),
                'model' => $this->groqModel(),
            ]);

            return false;
        }
    }

    /**
     * Prueba directa a Groq (no cacheada). Útil para depurar cuando isAvailable() falla.
     *
     * @return array{ok: bool, http_status: int|null, model: string, groq_error: string|null, exception: string|null}
     */
    public function diagnoseGroq(): array
    {
        $apiKey = config('services.groq.key');
        $model = $this->groqModel();

        if ($apiKey === '') {
            return [
                'ok' => false,
                'http_status' => null,
                'model' => $model,
                'groq_error' => 'GROQ_API_KEY vacía o solo espacios (revisa .env y ejecuta php artisan config:clear)',
                'exception' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
                ->timeout(15)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'ping'],
                    ],
                    'max_tokens' => 1,
                ]);

            $json = $response->json();
            $groqError = is_array($json) && isset($json['error']['message'])
                ? (string) $json['error']['message']
                : null;

            $ok = $response->successful() && isset($json['choices'][0]['message']['content']);

            return [
                'ok' => $ok,
                'http_status' => $response->status(),
                'model' => $model,
                'groq_error' => $groqError,
                'exception' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'http_status' => null,
                'model' => $model,
                'groq_error' => null,
                'exception' => $e->getMessage(),
            ];
        }
    }
}

