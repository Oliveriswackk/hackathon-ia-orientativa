<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GroqService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    /**
     * Envía un mensaje a Groq API y retorna la respuesta como string.
     * 
     * @param array $messages Array de mensajes en formato OpenAI
     * @return string Texto de la respuesta del modelo
     * @throws Exception Si la llamada falla o la respuesta es inválida
     */
    public function chat(array $messages): string
    {
        if (empty($this->apiKey)) {
            throw new Exception('GROQ_API_KEY no configurada');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])
        ->timeout(30)
        ->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $messages,
            'max_tokens' => 500,
        ]);

        $json = $response->json();

        if (!isset($json['choices'])) {
            $errorMessage = $json['error']['message'] ?? json_encode($json);
            throw new Exception('Error de Groq: ' . $errorMessage);
        }

        return $json['choices'][0]['message']['content'];
    }

    /**
     * Envía un mensaje a Groq API con contexto normativo.
     * 
     * @param array $messages Array de mensajes en formato OpenAI
     * @param array|null $documentoNormativo Documento normativo encontrado
     * @return string Texto de la respuesta del modelo
     * @throws Exception Si la llamada falla o la respuesta es inválida
     */
    public function chatConNormativa(array $messages, ?array $documentoNormativo = null): string
    {
        // Si hay documento normativo, construir el contexto y agregarlo al system prompt
        if ($documentoNormativo !== null) {
            $reglas = $documentoNormativo['reglas'] ?? [];
            $links = $documentoNormativo['links_oficiales'] ?? [];
            $linksTexto = is_array($links) ? implode(', ', $links) : (string)$links;

            $contextoNormativo = "BASE NORMATIVA OFICIAL (usa SOLO estos datos, nunca inventes plazos):\n" .
                "Tipo: {$documentoNormativo['nombre']}\n" .
                "Plazo: {$reglas['plazo_dias']} días {$reglas['tipo_dias']}\n" .
                "Desde: {$reglas['inicio_plazo']}\n" .
                "Fundamento legal: {$reglas['fundamento']}\n" .
                "Consecuencia si no actúas: {$reglas['consecuencia_fuera_plazo']}\n" .
                "Autoridad: {$documentoNormativo['autoridad']}\n" .
                "Ruta: {$documentoNormativo['ruta']}\n" .
                "Links oficiales: {$linksTexto}";

            // Modificar el primer mensaje (system) para incluir el contexto
            if (isset($messages[0]) && $messages[0]['role'] === 'system') {
                $messages[0]['content'] = $contextoNormativo . "\n\n" . $messages[0]['content'];
            } else {
                // Si no hay system message, agregarlo al inicio
                array_unshift($messages, [
                    'role' => 'system',
                    'content' => $contextoNormativo
                ]);
            }
        }

        // Llamar al método chat() existente
        return $this->chat($messages);
    }

    /**
     * Consulta urgente con timeout de 5 segundos.
     * 
     * @param string $situacion Situación a consultar
     * @return array Array con accion_inmediata, autoridad, link, nivel_urgencia
     */
    public function chatUrgencia(string $situacion): array
    {
        $systemPrompt = "Responde ÚNICAMENTE en JSON válido sin texto adicional:\n" .
            "{\"accion_inmediata\": string, \"autoridad\": string, \"link\": string, \"nivel_urgencia\": string}\n" .
            "Máximo 1 oración por campo. Solo temas electorales mexicanos.";

        try {
            if (empty($this->apiKey)) {
                throw new Exception('GROQ_API_KEY no configurada');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(5) // Timeout de 5 segundos para urgencia
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $situacion]
                ],
                'max_tokens' => 300,
            ]);

            $json = $response->json();

            if (!isset($json['choices'])) {
                throw new Exception('Error de Groq: ' . ($json['error']['message'] ?? 'Respuesta inválida'));
            }

            $respuesta = $json['choices'][0]['message']['content'];
            $parsed = json_decode($respuesta, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['accion_inmediata'])) {
                return $parsed;
            }

            // Si no es JSON válido, lanzar excepción para usar fallback
            throw new Exception('Respuesta no es JSON válido');

        } catch (\Exception $e) {
            // Fallback en caso de error o timeout
            return [
                'accion_inmediata' => 'Acude al tribunal electoral de tu estado hoy mismo',
                'autoridad' => 'Tribunal Electoral local',
                'link' => 'https://www.te.gob.mx',
                'nivel_urgencia' => 'ALTO'
            ];
        }
    }
}

