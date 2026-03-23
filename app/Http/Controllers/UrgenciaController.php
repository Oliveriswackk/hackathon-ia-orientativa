<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AI\AIManager;

class UrgenciaController extends Controller
{
    public function __construct(protected AIManager $aiManager) {}

    public function consultar(Request $request): JsonResponse
    {
        $situacion = $request->input('situacion', '');

        if (empty($situacion)) {
            return response()->json(['error' => 'Describe tu situación'], 400);
        }

        try {
            $systemPrompt = "Responde ÚNICAMENTE en JSON válido sin texto adicional:\n" .
                "{\"accion_inmediata\": string, \"autoridad\": string, \"link\": string, \"nivel_urgencia\": string}\n" .
                "Máximo 1 oración por campo. Solo temas electorales mexicanos.";

            $respuesta = $this->aiManager->ask($situacion, $systemPrompt);

            $parsed = json_decode($respuesta, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['accion_inmediata'])) {
                return response()->json($parsed);
            }

            throw new \RuntimeException('Respuesta no es JSON válido');
        } catch (\Throwable $e) {
            // Fallback (mismo payload que el GroqService anterior).
            return response()->json([
                'accion_inmediata' => 'Acude al tribunal electoral de tu estado hoy mismo',
                'autoridad' => 'Tribunal Electoral local',
                'link' => 'https://www.te.gob.mx',
                'nivel_urgencia' => 'ALTO',
            ]);
        }
    }
}

