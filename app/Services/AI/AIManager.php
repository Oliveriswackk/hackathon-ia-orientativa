<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIManager
{
    public function __construct(
        protected LlamaProvider $llamaProvider,
        protected OllamaProvider $ollamaProvider,
    ) {}

    public function activeProvider(): string
    {
        return $this->isLlamaAvailableCached() ? 'llama' : 'ollama';
    }

    public function ask(string $prompt, string $systemPrompt = ''): string
    {
        if (!$this->isLlamaAvailableCached() && !$this->isOllamaAvailableCached()) {
            throw new \RuntimeException('Conectividad: no hay conexión con LLaMA (llama) ni respuesta disponible desde Ollama (ollama).');
        }

        $first = $this->activeProvider();
        $second = $first === 'llama' ? 'ollama' : 'llama';

        try {
            $result = $this->getProvider($first)->ask($prompt, $systemPrompt);
            Log::info('AI request success', ['provider' => $first]);
            return $result;
        } catch (\Throwable $e) {
            Log::warning('AI request failed, fallback', [
                'provider' => $first,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback al otro proveedor (aunque la disponibilidad esté cacheada).
        try {
            $result = $this->getProvider($second)->ask($prompt, $systemPrompt);
            Log::info('AI request success (fallback)', ['provider' => $second]);
            return $result;
        } catch (\Throwable $e) {
            $message = "Ningún proveedor de IA pudo responder. " .
                "Conectividad/modelo: llama y ollama fallaron. " .
                "Error último: " . $e->getMessage();

            Log::error('AI request failed (both providers)', [
                'llama_available' => $this->isLlamaAvailableCached(),
                'ollama_available' => $this->isOllamaAvailableCached(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, previous: $e);
        }
    }

    public function askWithContext(array $context, string $question): string
    {
        if (!$this->isLlamaAvailableCached() && !$this->isOllamaAvailableCached()) {
            throw new \RuntimeException('Conectividad: no hay conexión con LLaMA (llama) ni respuesta disponible desde Ollama (ollama).');
        }

        $first = $this->activeProvider();
        $second = $first === 'llama' ? 'ollama' : 'llama';

        try {
            $result = $this->getProvider($first)->askWithContext($context, $question);
            Log::info('AI request success', ['provider' => $first]);
            return $result;
        } catch (\Throwable $e) {
            Log::warning('AI request failed, fallback', [
                'provider' => $first,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $result = $this->getProvider($second)->askWithContext($context, $question);
            Log::info('AI request success (fallback)', ['provider' => $second]);
            return $result;
        } catch (\Throwable $e) {
            $message = "Ningún proveedor de IA pudo responder (con contexto). " .
                "Conectividad/modelo: llama y ollama fallaron. " .
                "Error último: " . $e->getMessage();

            Log::error('AI request failed (both providers) with context', [
                'llama_available' => $this->isLlamaAvailableCached(),
                'ollama_available' => $this->isOllamaAvailableCached(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, previous: $e);
        }
    }

    public function isLlamaAvailable(): bool
    {
        return $this->isLlamaAvailableCached();
    }

    public function isOllamaAvailable(): bool
    {
        return $this->isOllamaAvailableCached();
    }

    /**
     * Ejecuta ask() devolviendo metadatos para la API (proveedor usado, fallback, etc.).
     *
     * @return array{
     *   answer: string,
     *   internal_provider: 'llama'|'ollama',
     *   used_fallback: bool,
     *   first_provider_error: string|null
     * }
     */
    public function askWithTelemetry(string $prompt, string $systemPrompt = ''): array
    {
        if (!$this->isLlamaAvailableCached() && !$this->isOllamaAvailableCached()) {
            throw new \RuntimeException(
                'Conectividad: no hay conexión con LLaMA (llama) ni respuesta disponible desde Ollama (ollama).'
            );
        }

        $first = $this->activeProvider();
        $second = $first === 'llama' ? 'ollama' : 'llama';
        $firstError = null;

        try {
            $result = $this->getProvider($first)->ask($prompt, $systemPrompt);
            Log::info('AI request success', ['provider' => $first]);

            return [
                'answer' => $result,
                'internal_provider' => $first,
                'used_fallback' => false,
                'first_provider_error' => null,
            ];
        } catch (\Throwable $e) {
            $firstError = $e->getMessage();
            Log::warning('AI request failed, fallback', [
                'provider' => $first,
                'error' => $firstError,
            ]);
        }

        try {
            $result = $this->getProvider($second)->ask($prompt, $systemPrompt);
            Log::info('AI request success (fallback)', ['provider' => $second]);

            return [
                'answer' => $result,
                'internal_provider' => $second,
                'used_fallback' => true,
                'first_provider_error' => $firstError,
            ];
        } catch (\Throwable $e) {
            $message = 'Ningún proveedor de IA pudo responder. ' .
                'Conectividad/modelo: llama y ollama fallaron. ' .
                'Error último: ' . $e->getMessage();

            Log::error('AI request failed (both providers)', [
                'llama_available' => $this->isLlamaAvailableCached(),
                'ollama_available' => $this->isOllamaAvailableCached(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, previous: $e);
        }
    }

    /**
     * Igual que askWithTelemetry pero intentando primero el proveedor indicado (llama|ollama),
     * útil para comparar Groq vs Ollama en paralelo desde el frontend.
     *
     * @param  'llama'|'ollama'  $preferredInternal
     * @return array{
     *   answer: string,
     *   internal_provider: 'llama'|'ollama',
     *   used_fallback: bool,
     *   first_provider_error: string|null
     * }
     */
    public function askWithTelemetryPreferring(string $preferredInternal, string $prompt, string $systemPrompt = ''): array
    {
        if (!in_array($preferredInternal, ['llama', 'ollama'], true)) {
            throw new \InvalidArgumentException('Proveedor interno inválido: ' . $preferredInternal);
        }

        if (!$this->isLlamaAvailableCached() && !$this->isOllamaAvailableCached()) {
            throw new \RuntimeException(
                'Conectividad: no hay conexión con LLaMA (llama) ni respuesta disponible desde Ollama (ollama).'
            );
        }

        $first = $preferredInternal;
        $second = $first === 'llama' ? 'ollama' : 'llama';
        $firstError = null;

        try {
            $result = $this->getProvider($first)->ask($prompt, $systemPrompt);
            Log::info('AI request success', ['provider' => $first, 'preferred' => true]);

            return [
                'answer' => $result,
                'internal_provider' => $first,
                'used_fallback' => false,
                'first_provider_error' => null,
            ];
        } catch (\Throwable $e) {
            $firstError = $e->getMessage();
            Log::warning('AI request failed, fallback (preferente)', [
                'provider' => $first,
                'error' => $firstError,
            ]);
        }

        try {
            $result = $this->getProvider($second)->ask($prompt, $systemPrompt);
            Log::info('AI request success (fallback, preferente)', ['provider' => $second]);

            return [
                'answer' => $result,
                'internal_provider' => $second,
                'used_fallback' => true,
                'first_provider_error' => $firstError,
            ];
        } catch (\Throwable $e) {
            $message = 'Ningún proveedor de IA pudo responder. ' .
                'Conectividad/modelo: llama y ollama fallaron. ' .
                'Error último: ' . $e->getMessage();

            Log::error('AI request failed (both providers, preferente)', [
                'llama_available' => $this->isLlamaAvailableCached(),
                'ollama_available' => $this->isOllamaAvailableCached(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, previous: $e);
        }
    }

    /**
     * Igual que askWithContext pero con telemetría para la capa HTTP.
     *
     * @return array{
     *   answer: string,
     *   internal_provider: 'llama'|'ollama',
     *   used_fallback: bool,
     *   first_provider_error: string|null
     * }
     */
    public function askWithContextWithTelemetry(array $context, string $question): array
    {
        if (!$this->isLlamaAvailableCached() && !$this->isOllamaAvailableCached()) {
            throw new \RuntimeException(
                'Conectividad: no hay conexión con LLaMA (llama) ni respuesta disponible desde Ollama (ollama).'
            );
        }

        $first = $this->activeProvider();
        $second = $first === 'llama' ? 'ollama' : 'llama';
        $firstError = null;

        try {
            $result = $this->getProvider($first)->askWithContext($context, $question);
            Log::info('AI request success', ['provider' => $first, 'with_context' => true]);

            return [
                'answer' => $result,
                'internal_provider' => $first,
                'used_fallback' => false,
                'first_provider_error' => null,
            ];
        } catch (\Throwable $e) {
            $firstError = $e->getMessage();
            Log::warning('AI request failed, fallback', [
                'provider' => $first,
                'error' => $firstError,
                'with_context' => true,
            ]);
        }

        try {
            $result = $this->getProvider($second)->askWithContext($context, $question);
            Log::info('AI request success (fallback)', ['provider' => $second, 'with_context' => true]);

            return [
                'answer' => $result,
                'internal_provider' => $second,
                'used_fallback' => true,
                'first_provider_error' => $firstError,
            ];
        } catch (\Throwable $e) {
            $message = 'Ningún proveedor de IA pudo responder (con contexto). ' .
                'Conectividad/modelo: llama y ollama fallaron. ' .
                'Error último: ' . $e->getMessage();

            Log::error('AI request failed (both providers) with context', [
                'llama_available' => $this->isLlamaAvailableCached(),
                'ollama_available' => $this->isOllamaAvailableCached(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, previous: $e);
        }
    }

    /**
     * Genera 3 preguntas de seguimiento usando el mismo proveedor interno que respondió.
     *
     * @param  'llama'|'ollama'  $internalProvider
     * @return list<string>
     */
    public function suggestFollowUpQuestions(
        string $userQuestion,
        string $assistantAnswer,
        string $internalProvider
    ): array {
        $system = 'Eres un asistente electoral. Responde ÚNICAMENTE con JSON válido, sin markdown ni texto fuera del JSON. ' .
            'Formato exacto: {"suggested_questions":["pregunta1","pregunta2","pregunta3"]}. ' .
            'Las 3 preguntas deben estar en español, ser breves y ser útiles como seguimiento.';

        $user = "Pregunta del usuario:\n" . mb_substr($userQuestion, 0, 4000) . "\n\n" .
            "Respuesta del asistente:\n" . mb_substr($assistantAnswer, 0, 6000) . "\n\n" .
            'Genera exactamente 3 preguntas de seguimiento.';

        try {
            $raw = $this->getProvider($internalProvider)->ask($user, $system);
            $list = $this->parseFollowUpQuestionsPayload($raw);
            if (count($list) >= 3) {
                return [mb_substr($list[0], 0, 500), mb_substr($list[1], 0, 500), mb_substr($list[2], 0, 500)];
            }
            if (count($list) > 0) {
                $padded = array_values(array_pad($list, 3, $list[0]));

                return [
                    mb_substr($padded[0], 0, 500),
                    mb_substr($padded[1], 0, 500),
                    mb_substr($padded[2], 0, 500),
                ];
            }
        } catch (\Throwable $e) {
            Log::notice('No se pudieron generar suggested_questions por modelo', [
                'provider' => $internalProvider,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->defaultFollowUpQuestions();
    }

    /**
     * @return list<string>
     */
    protected function defaultFollowUpQuestions(): array
    {
        return [
            '¿Puedes concretar los plazos que aplican en mi caso?',
            '¿Qué documento o notificación debo revisar primero?',
            '¿A qué autoridad electoral debo acudir para confirmarlo?',
        ];
    }

    /**
     * @return list<string>
     */
    protected function parseFollowUpQuestionsPayload(string $raw): array
    {
        $trimmed = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $trimmed, $m)) {
            $trimmed = trim($m[1]);
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded) || !isset($decoded['suggested_questions']) || !is_array($decoded['suggested_questions'])) {
            return [];
        }

        $out = [];
        foreach ($decoded['suggested_questions'] as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = trim($item);
            }
        }

        return $out;
    }

    protected function getProvider(string $name): AIProviderInterface
    {
        return match ($name) {
            'llama' => $this->llamaProvider,
            'ollama' => $this->ollamaProvider,
            default => throw new \InvalidArgumentException("Proveedor IA inválido: {$name}"),
        };
    }

    protected function isLlamaAvailableCached(): bool
    {
        return Cache::remember(
            'ai.available.llama',
            60,
            fn () => $this->llamaProvider->isAvailable()
        );
    }

    protected function isOllamaAvailableCached(): bool
    {
        return Cache::remember(
            'ai.available.ollama',
            60,
            fn () => $this->ollamaProvider->isAvailable()
        );
    }
}

