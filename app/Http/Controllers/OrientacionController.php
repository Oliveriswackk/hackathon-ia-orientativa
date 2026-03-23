<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AI\AIManager;

class OrientacionController extends Controller
{
    public function __construct(protected AIManager $aiManager) {}

    public function mensaje(Request $request): JsonResponse
    {
        $mensaje = $request->input('mensaje', '');
        $historial = $request->input('historial', []);
        $preguntasRealizadas = (int)$request->input('preguntas_realizadas', 0);

        if (empty($mensaje)) {
            return response()->json(['error' => 'El mensaje no puede estar vacío'], 400);
        }

        try {
            $systemPrompt = "Eres ORIENTA, orientador de justicia electoral mexicana. Público: jóvenes 18-29 años sin experiencia jurídica. REGLAS: 1) Haz UNA sola pregunta a la vez. 2) Máximo 5 preguntas antes del diagnóstico. 3) Solo temas electorales mexicanos. 4) Lenguaje simple, como hablando con un amigo. 5) Nunca inventes plazos.";

            // Si ya se realizaron 5 preguntas, agregar instrucción para diagnóstico final
            if ($preguntasRealizadas >= 5) {
                $systemPrompt .= "\n\nEl usuario ya respondió 5 preguntas. Da diagnóstico final ÚNICAMENTE en JSON:\n" .
                    "{\"diagnostico\": string, \"ruta_sugerida\": string, \"nivel_urgencia\": string, \"accion_inmediata\": string}";
            }

            // Convertir historial a un prompt de texto (para compatibilidad con AIProviderInterface::ask()).
            $promptParts = [];
            foreach ($historial as $msg) {
                $role = $msg['role'] ?? 'user';
                $content = $msg['content'] ?? '';
                $promptParts[] = "{$role}: {$content}";
            }
            $promptParts[] = "user: {$mensaje}";
            $prompt = implode("\n\n", $promptParts);

            $response = $this->aiManager->ask($prompt, $systemPrompt);

            $preguntasRealizadas++;
            $esDiagnosticoFinal = false;

            // Detectar si la respuesta es JSON (empieza con {)
            $respuestaTrim = trim($response);
            if (mb_substr($respuestaTrim, 0, 1) === '{') {
                $json = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($json['diagnostico'])) {
                    $esDiagnosticoFinal = true;
                    $historialActualizado = array_merge($historial, [
                        ['role' => 'user', 'content' => $mensaje],
                        ['role' => 'assistant', 'content' => $response]
                    ]);

                    return response()->json([
                        'respuesta' => '',
                        'historial' => $historialActualizado,
                        'preguntas_realizadas' => $preguntasRealizadas,
                        'es_diagnostico_final' => true,
                        'diagnostico' => $json
                    ]);
                }
            }

            // Agregar respuesta al historial
            $historialActualizado = array_merge($historial, [
                ['role' => 'user', 'content' => $mensaje],
                ['role' => 'assistant', 'content' => $response]
            ]);

            return response()->json([
                'respuesta' => $response,
                'historial' => $historialActualizado,
                'preguntas_realizadas' => $preguntasRealizadas,
                'es_diagnostico_final' => $esDiagnosticoFinal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }
}

