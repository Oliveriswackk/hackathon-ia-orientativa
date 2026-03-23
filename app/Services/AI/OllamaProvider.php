<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements AIProviderInterface
{
    protected string $baseUrl = 'http://localhost:11434';
    protected string $model = 'phi3';

    public function ask(string $prompt, string $systemPrompt = ''): string
    {
        $baseSystemPrompt = $this->getPhi3BaseSystemPrompt();

        $finalSystemPrompt = $baseSystemPrompt;
        if (!empty($systemPrompt)) {
            $finalSystemPrompt .= "\n\n" . $systemPrompt;
        }

        $messages = [];

        if (!empty($finalSystemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $finalSystemPrompt,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $response = Http::timeout(120)->post("{$this->baseUrl}/api/chat", [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'num_ctx' => 8192,
                'temperature' => 0.2,
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Error en Ollama: ' . ($response->body() ?: $response->status()));
        }

        // Estructura esperada por Ollama: { message: { content: "..." } }
        return $response->json('message.content') ?? 'Sin respuesta';
    }

    public function askWithContext(array $context, string $question): string
    {
        // `ask()` ya antepone system_prompt/rules_desarrollo del JSON.
        $extraSystemPrompt = $this->buildSystemPrompt($context);
        return $this->ask($question, $extraSystemPrompt);
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Construye el system prompt para Phi-3 con:
     * - system_prompt y rules_desarrollo desde storage/app/contexto_jdc007.json
     * - contexto JSON inyectado (si se pasó como parámetro)
     */
    protected function buildSystemPrompt(array $context): string
    {
        $systemPrompt = '';

        // Soporte: si el llamador ya incluye instrucciones en `system_prompt`, respétalas.
        if (!empty($context['system_prompt'])) {
            $systemPrompt .= (string) $context['system_prompt'] . "\n\n";
        }

        // Formato compatible con lo que ya usaba OllamaService.
        if (!empty($context['project'])) {
            $systemPrompt .= "Proyecto: {$context['project']}\n\n";
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
        }

        // Inyección genérica (por compatibilidad) si el contexto no sigue el shape esperado.
        if (empty($context['files']) && empty($context['project']) && !empty($context)) {
            $systemPrompt .= "Contexto (JSON):\n" .
                json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
                "\n\n";
        }

        if (!empty($systemPrompt)) {
            $systemPrompt .= "Usa el contexto anterior para responder con precisión.";
        }

        return $systemPrompt;
    }

    protected function getPhi3BaseSystemPrompt(): string
    {
        $contextPath = storage_path('app/contexto_jdc007.json');
        if (!file_exists($contextPath)) {
            throw new \RuntimeException('Falta contexto Phi-3: ' . $contextPath);
        }

        $raw = file_get_contents($contextPath);
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new \RuntimeException('contexto_jdc007.json inválido (JSON no decodificable)');
        }

        $systemPrompt = '';

        $fromFileSystemPrompt = $json['system_prompt'] ?? '';
        $rules = $json['rules_desarrollo'] ?? null;

        if (!empty($fromFileSystemPrompt)) {
            $systemPrompt .= $fromFileSystemPrompt . "\n\n";
        }

        if (!empty($rules)) {
            if (is_array($rules)) {
                $systemPrompt .= "rules_desarrollo:\n" . implode("\n", $rules) . "\n\n";
            } else {
                $systemPrompt .= "rules_desarrollo:\n" . (string)$rules . "\n\n";
            }
        }

        $systemPrompt .= "Responde siguiendo exclusivamente las rules_desarrollo. Respuesta en español.";

        return $systemPrompt;
    }
}

