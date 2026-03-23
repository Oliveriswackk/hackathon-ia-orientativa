<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected string $baseUrl = 'http://localhost:11434';
    protected string $model   = 'phi3';

    public function ask(string $question, string $systemPrompt = ''): string
    {
        $messages = [];

        if (!empty($systemPrompt)) {
            $messages[] = [
                'role'    => 'system',
                'content' => $systemPrompt,
            ];
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $question,
        ];

        $response = Http::timeout(120)->post("{$this->baseUrl}/api/chat", [
            'model'    => $this->model,
            'messages' => $messages,
            'stream'   => false,
            'options'  => [
                'num_ctx'     => 8192,
                'temperature' => 0.2,
            ],
        ]);

        return $response->json('message.content') ?? 'Sin respuesta';
    }

    public function askWithContext(array $context, string $question): string
    {
        // Construir el system prompt con tus archivos JSON
        $systemPrompt = "Proyecto: {$context['project']}\n\n";

        foreach ($context['files'] as $file) {
            $systemPrompt .= "--- Archivo: {$file['name']} ---\n";

            if (!empty($file['description'])) {
                $systemPrompt .= "Descripción: {$file['description']}\n";
            }

            $systemPrompt .= "{$file['content']}\n\n";
        }

        $systemPrompt .= "Usa el contexto anterior para responder con precisión.";

        return $this->ask($question, $systemPrompt);
    }
}