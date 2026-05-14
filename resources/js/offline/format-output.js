/**
 * @param {import('./types.js').OutputFormat} output
 */
export function formatOutput(output) {
    const lines = [];
    if (output.tipo_doc) lines.push(`Tipo: ${output.tipo_doc}`);
    if (output.autoridad_competente) lines.push(`Autoridad: ${output.autoridad_competente}`);
    if (output.ruta_sugerida) lines.push(`Ruta sugerida: ${output.ruta_sugerida}`);
    if (output.tramite) lines.push(`Trámite: ${output.tramite}`);
    if (output.modalidad) lines.push(`Modalidad: ${output.modalidad}`);
    if (output.plazo) lines.push(`Plazo: ${output.plazo}`);
    if (output.consecuencias) lines.push(`Consecuencias: ${output.consecuencias}`);
    if (output.respuesta) lines.push(`\n${output.respuesta}`);
    if (output.links_oficiales?.length) {
        lines.push(`\nLinks oficiales:\n- ${output.links_oficiales.join('\n- ')}`);
    }
    return lines.join('\n');
}
