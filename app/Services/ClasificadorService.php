<?php

namespace App\Services;

use Exception;

class ClasificadorService
{
    /**
     * Clasifica un texto según las palabras clave de los documentos normativos.
     * 
     * @param string $texto Texto a clasificar
     * @return array|null Documento con más coincidencias o null si no hay coincidencias
     */
    public function clasificar(string $texto): ?array
    {
        $rutaNormativa = storage_path('app/normativa.json');
        
        if (!file_exists($rutaNormativa)) {
            throw new Exception('El archivo normativa.json no existe en storage/app/');
        }

        $contenido = file_get_contents($rutaNormativa);
        $normativa = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al parsear normativa.json: ' . json_last_error_msg());
        }

        if (!isset($normativa['documentos'])) {
            throw new Exception('El archivo normativa.json no tiene la estructura esperada');
        }

        $textoLower = mb_strtolower($texto, 'UTF-8');
        $mejorCoincidencia = null;
        $maxCoincidencias = 0;

        foreach ($normativa['documentos'] as $clave => $documento) {
            if (!isset($documento['palabras_clave']) || !is_array($documento['palabras_clave'])) {
                continue;
            }

            $coincidencias = 0;
            foreach ($documento['palabras_clave'] as $palabra) {
                $palabraLower = mb_strtolower($palabra, 'UTF-8');
                if (mb_strpos($textoLower, $palabraLower) !== false) {
                    $coincidencias++;
                }
            }

            if ($coincidencias > $maxCoincidencias) {
                $maxCoincidencias = $coincidencias;
                $mejorCoincidencia = $documento;
                $mejorCoincidencia['_clave'] = $clave;
            }
        }

        // Retornar solo si hay al menos 1 coincidencia
        return $maxCoincidencias > 0 ? $mejorCoincidencia : null;
    }
}

