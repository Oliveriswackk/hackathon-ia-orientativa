<?php

namespace App\Http\Controllers;

use App\Services\ClasificadorService;
use App\Services\AI\AIManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TraductorController extends Controller
{
    public function __construct(protected AIManager $aiManager) {}

    public function analizar(Request $request): JsonResponse
    {
        $texto = $request->input('texto', '');
        $tipo = $request->input('tipo', '');

        if (empty($texto)) {
            return response()->json(['error' => 'Ingresa texto para analizar'], 400);
        }

        try {
            $clasificador = new ClasificadorService();

            // Clasificar el texto
            $documentoNormativo = $clasificador->clasificar($texto);

            // Si el usuario seleccionó un tipo manualmente Y el clasificador no encontró coincidencias,
            // intentar buscar el documento por el tipo seleccionado en el JSON
            if ($documentoNormativo === null && !empty($tipo)) {
                $rutaNormativa = storage_path('app/normativa.json');
                if (file_exists($rutaNormativa)) {
                    $normativa = json_decode(file_get_contents($rutaNormativa), true);
                    $mapeoTipos = [
                        'acuerdo_requerimiento' => 'acuerdo_requerimiento',
                        'notificacion_resolucion' => 'cedula_notificacion',
                        'resolucion_medio' => 'resolucion_medio_impugnacion',
                        'acuerdo_desechamiento' => 'acuerdo_desechamiento'
                    ];
                    
                    if (isset($mapeoTipos[$tipo]) && isset($normativa['documentos'][$mapeoTipos[$tipo]])) {
                        $documentoNormativo = $normativa['documentos'][$mapeoTipos[$tipo]];
                        $documentoNormativo['_clave'] = $mapeoTipos[$tipo];
                    }
                }
            }

            $systemPrompt = "Eres ORIENTA, asistente de justicia electoral mexicana para jóvenes de 18-29 años sin experiencia jurídica. Analiza el documento y responde ÚNICAMENTE en JSON válido sin texto adicional ni bloques de código:\n" .
                "{\"tipo_documento\": string, \"plazo_dias\": number, \"tipo_dias\": string, \"inicio_computo\": string, \"consecuencia\": string, \"autoridad\": string, \"link_oficial\": string, \"traduccion_simple\": string, \"nivel_urgencia\": string, \"accion_inmediata\": string}\n" .
                "IMPORTANTE: Los plazos SOLO vienen de la BASE NORMATIVA que se te proporcionó.\n" .
                "traduccion_simple debe ser máximo 3 oraciones en lenguaje de joven de 20 años.";

            $userMessage = "Tipo seleccionado: {$tipo}\n\nDocumento:\n{$texto}";

            $response = $this->aiManager->askWithContext(
                [
                    'system_prompt' => $systemPrompt,
                    'documento_normativo' => $documentoNormativo,
                ],
                $userMessage
            );

            // Intentar parsear como JSON
            $json = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Si falla el parse, verificar si la IA no pudo determinar el tipo
                if (stripos($response, 'no puedo') !== false || stripos($response, 'no es posible') !== false) {
                    return response()->json([
                        'error' => 'No se pudo determinar el tipo de documento. Intenta pegar más texto del documento o selecciona el tipo manualmente.'
                    ], 400);
                }
                
                // Si falla el parse, retornar el texto crudo como traduccion_simple
                return response()->json([
                    'tipo_documento' => 'Documento electoral',
                    'plazo_dias' => 0,
                    'tipo_dias' => 'hábiles',
                    'inicio_computo' => 'Confirma con la autoridad que emitió el documento',
                    'consecuencia' => 'Puede haber pérdida de derecho para impugnar o continuar el trámite',
                    'autoridad' => 'Autoridad electoral competente',
                    'link_oficial' => 'https://tepjf.gob.mx',
                    'traduccion_simple' => $response,
                    'nivel_urgencia' => 'MEDIO',
                    'accion_inmediata' => 'Acude o comunícate con la autoridad que aparece en tu documento para confirmar plazos y pasos'
                ]);
            }

            // Asegurar que todos los campos existan
            $result = [
                'tipo_documento' => $json['tipo_documento'] ?? 'Documento electoral',
                'plazo_dias' => (int)($json['plazo_dias'] ?? 0),
                'tipo_dias' => $json['tipo_dias'] ?? 'hábiles',
                'inicio_computo' => $json['inicio_computo'] ?? 'Confirma con la autoridad',
                'consecuencia' => $json['consecuencia'] ?? 'Puede haber pérdida de derecho',
                'autoridad' => $json['autoridad'] ?? 'Autoridad electoral competente',
                'link_oficial' => $json['link_oficial'] ?? 'https://tepjf.gob.mx',
                'traduccion_simple' => $json['traduccion_simple'] ?? 'Documento electoral que requiere atención',
                'nivel_urgencia' => $json['nivel_urgencia'] ?? 'MEDIO',
                'accion_inmediata' => $json['accion_inmediata'] ?? 'Acude a la autoridad indicada'
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al analizar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
}

