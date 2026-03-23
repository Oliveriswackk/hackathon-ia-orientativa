<?php

namespace App\Http\Controllers;

use App\Services\AI\AIManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * API de consultas IA con formato JSON unificado, fallback entre proveedores y preguntas sugeridas.
 */
class AIController extends Controller
{
    public function __construct(protected AIManager $aiManager) {}

    /**
     * Pregunta simple (sin contexto de archivos).
     */
    public function ask(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|min:1|max:16000',
                'system_prompt' => 'nullable|string|max:12000',
                // groq | ollama: intentar primero ese proveedor (con fallback si falla)
                'provider' => 'nullable|string|in:groq,ollama',
            ]);
        } catch (ValidationException $e) {
            return $this->errorEnvelope(
                $this->firstValidationMessage($e),
                422
            );
        }

        $question = $validated['question'];
        $systemPrompt = $validated['system_prompt'] ?? '';
        $preferredPublic = $validated['provider'] ?? null;

        $t0 = microtime(true);

        try {
            $telemetry = match ($preferredPublic) {
                'groq' => $this->aiManager->askWithTelemetryPreferring('llama', $question, $systemPrompt),
                'ollama' => $this->aiManager->askWithTelemetryPreferring('ollama', $question, $systemPrompt),
                default => $this->aiManager->askWithTelemetry($question, $systemPrompt),
            };
        } catch (\Throwable $e) {
            $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
            Log::error('AIController::ask fallo total', [
                'elapsed_ms' => $elapsedMs,
                'error' => $e->getMessage(),
            ]);

            return $this->errorEnvelope(
                $e->getMessage() ?: 'No fue posible obtener respuesta de la IA.',
                503
            );
        }

        $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
        $internal = $telemetry['internal_provider'];
        $provider = $this->mapProviderPublic($internal);
        $mode = $this->mapModePublic($internal);

        Log::info('AIController::ask completado', [
            'provider_used' => $provider,
            'mode' => $mode,
            'used_fallback' => $telemetry['used_fallback'],
            'elapsed_ms' => $elapsedMs,
        ]);

        $suggested = $this->aiManager->suggestFollowUpQuestions(
            $question,
            $telemetry['answer'],
            $internal
        );

        return response()->json(
            $this->successEnvelope(
                [
                    'answer' => $telemetry['answer'],
                    'suggested_questions' => $suggested,
                    'fallback_used' => $telemetry['used_fallback'],
                    'response_time_ms' => $elapsedMs,
                    'fallback_notice' => $telemetry['used_fallback']
                        ? $this->fallbackNotice($telemetry['first_provider_error'])
                        : null,
                    /** Proveedor que pidió el cliente (si lo envió); el que respondió está en la raíz `provider`. */
                    'requested_provider' => $preferredPublic,
                ],
                $provider,
                $mode
            )
        );
    }

    /**
     * Pregunta con contexto (archivos, proyecto, normativa, etc.).
     */
    public function askWithContext(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|min:1|max:16000',
                'context' => 'required|array',
            ]);
        } catch (ValidationException $e) {
            return $this->errorEnvelope(
                $this->firstValidationMessage($e),
                422
            );
        }

        $question = $validated['question'];
        $context = $validated['context'];

        $t0 = microtime(true);

        try {
            $telemetry = $this->aiManager->askWithContextWithTelemetry($context, $question);
        } catch (\Throwable $e) {
            $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
            Log::error('AIController::askWithContext fallo total', [
                'elapsed_ms' => $elapsedMs,
                'error' => $e->getMessage(),
            ]);

            return $this->errorEnvelope(
                $e->getMessage() ?: 'No fue posible obtener respuesta de la IA con contexto.',
                503
            );
        }

        $elapsedMs = (int) round((microtime(true) - $t0) * 1000);
        $internal = $telemetry['internal_provider'];
        $provider = $this->mapProviderPublic($internal);
        $mode = $this->mapModePublic($internal);

        Log::info('AIController::askWithContext completado', [
            'provider_used' => $provider,
            'mode' => $mode,
            'used_fallback' => $telemetry['used_fallback'],
            'elapsed_ms' => $elapsedMs,
        ]);

        $suggested = $this->aiManager->suggestFollowUpQuestions(
            $question,
            $telemetry['answer'],
            $internal
        );

        return response()->json(
            $this->successEnvelope(
                [
                    'answer' => $telemetry['answer'],
                    'suggested_questions' => $suggested,
                    'fallback_used' => $telemetry['used_fallback'],
                    'response_time_ms' => $elapsedMs,
                    'fallback_notice' => $telemetry['used_fallback']
                        ? $this->fallbackNotice($telemetry['first_provider_error'])
                        : null,
                ],
                $provider,
                $mode
            )
        );
    }

    /**
     * Convierte proveedor interno (llama) al nombre público de la API (groq).
     */
    protected function mapProviderPublic(string $internal): string
    {
        return $internal === 'llama' ? 'groq' : 'ollama';
    }

    /**
     * Modo de cara al usuario: Groq = online, Ollama = offline.
     */
    protected function mapModePublic(string $internal): string
    {
        return $internal === 'llama' ? 'online' : 'offline';
    }

    /**
     * Mensaje claro cuando hubo fallback al segundo proveedor.
     */
    protected function fallbackNotice(?string $firstError): string
    {
        $base = 'El proveedor principal no respondió; se usó el proveedor de respaldo.';
        if ($firstError) {
            return $base . ' Detalle (intento previo): ' . mb_substr($firstError, 0, 300);
        }

        return $base;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function successEnvelope(array $data, string $provider, string $mode): array
    {
        return [
            'success' => true,
            'data' => $data,
            'provider' => $provider,
            'mode' => $mode,
            'error' => null,
        ];
    }

    /**
     * Respuesta de error con la misma forma que los éxitos.
     */
    protected function errorEnvelope(string $message, int $http = 503): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => [
                'answer' => null,
                'suggested_questions' => [],
            ],
            'provider' => null,
            'mode' => null,
            'error' => $message,
        ], $http);
    }

    protected function firstValidationMessage(ValidationException $e): string
    {
        $messages = $e->validator->errors()->all();

        return $messages[0] ?? 'Los datos enviados no son válidos.';
    }
}
