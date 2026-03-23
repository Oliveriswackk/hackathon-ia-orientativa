<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Services\GroqService;
use Illuminate\Support\Facades\Http;

class LlamaProvider implements AIProviderInterface
{
    protected string $model = 'llama-3.3-70b-versatile';

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
        if (empty($apiKey)) {
            return false;
        }

        try {
            // Request ligero para comprobar conectividad real.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
                ->timeout(5)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'ping'],
                    ],
                    'max_tokens' => 1,
                ]);

            if (!$response->successful()) {
                return false;
            }

            $json = $response->json();
            return isset($json['choices'][0]['message']['content']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

