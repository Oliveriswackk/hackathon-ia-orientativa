/**
 * Compone el JSON de turno (UI) desde rule pack + contexto, sin LLM.
 * @param {import('./types.js').RulePack | null | undefined} rulePack
 * @param {import('./types.js').CatalogEntry | null} entry
 * @param {Record<string, string>} userContext respuestas del wizard (option id -> value)
 * @param {{ expedient?: string }} [extra]
 */
export function composeGuidanceTurn(rulePack, entry, userContext = {}, extra = {}) {
    const rp = rulePack || entry?.rule_pack;
    const urgencyLevel = rp?.reglas?.plazo_dias != null ? Math.min(5, 2 + Number(rp.reglas.plazo_dias)) : 2;

    const businessDays = rp?.reglas?.plazo_dias ?? null;
    const display =
        businessDays != null
            ? `Plazo orientativo precargado: ${businessDays} días (verifica en tu documento y la autoridad).`
            : 'Consulta las fechas en tu documento o con la autoridad competente.';

    const tipoDoc =
        entry?.output?.tipo_doc ||
        (rp?.doc_type ? rp.doc_type.replace(/_/g, ' ') : '') ||
        'Documento electoral';

    return {
        urgency: {
            level: urgencyLevel,
            label: urgencyLevel >= 4 ? 'alta' : urgencyLevel >= 3 ? 'media' : 'baja',
        },
        deadline: {
            business_days: businessDays,
            display,
        },
        document: {
            type: tipoDoc,
            expedient: extra.expedient || userContext.expedient || '',
        },
        summary: {
            meaning:
                entry?.summary ||
                'Existe información precargada relacionada con tu consulta. No sustituye asesoría legal.',
            risk: rp?.reglas?.consecuencia_fuera_plazo
                ? `Fuera de plazo: ${String(rp.reglas.consecuencia_fuera_plazo).replace(/_/g, ' ')}.`
                : 'Esperar puede limitar opciones legales.',
            next_steps: [
                'Revisar fechas importantes en tu notificación',
                'Identificar la autoridad competente',
                rp?.ruta_default ? `Ruta sugerida: ${rp.ruta_default}` : 'Consultar opciones disponibles',
            ],
        },
        support: {
            message: 'Lo importante es no ignorar el plazo y verificar siempre con la autoridad.',
            primary_cta: 'Continuar',
        },
        system: {
            source: 'offline_rule',
        },
    };
}
