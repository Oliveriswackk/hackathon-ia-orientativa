<?php
namespace App\Contracts;

interface AIProviderInterface
{
    /**
     * Pregunta simple con optional system prompt.
     */
    public function ask(string $prompt, string $systemPrompt = ''): string;

    /**
     * Pregunta con un contexto estructurado (JSON/array).
     */
    public function askWithContext(array $context, string $question): string;

    /**
     * Indica si el proveedor está disponible (conectividad real).
     */
    public function isAvailable(): bool;
}

