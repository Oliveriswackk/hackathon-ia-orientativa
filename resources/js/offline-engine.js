/**
 * Offline engine (sin LLM): selecciona un caso desde JSON y guía con preguntas.
 *
 * Diseñado para funcionar totalmente local (sin requests) cuando navigator.onLine === false.
 */

/**
 * @typedef {{ id: string, label: string, keywords?: string[] }} OfflineOption
 * @typedef {{ id: string, prompt: string, options: OfflineOption[] }} OfflineQuestion
 * @typedef {{
 *  tipo_doc: string,
 *  autoridad_competente: string,
 *  ruta_sugerida: string,
 *  tramite: string,
 *  modalidad: string,
 *  plazo: string,
 *  consecuencias: string,
 *  links_oficiales: string[],
 *  respuesta: string
 * }} OutputFormat
 * @typedef {{
 *  id: string,
 *  title: string,
 *  keywords: string[],
 *  summary: string,
 *  questions?: OfflineQuestion[],
 *  output: OutputFormat,
 *  sources: {label: string, url: string}[],
 *  suggested_questions?: string[]
 * }} OfflineCase
 */

/** @type {Record<string, { default: OfflineCase }>} */
const CASE_MODULES = import.meta.glob('../data/offline/cases/*.json', { eager: true });

/** @type {OfflineCase[]} */
const OFFLINE_CASES = Object.values(CASE_MODULES).map((m) => m.default);

function normalize(str) {
    return (str || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function scoreCase(qNorm, c) {
    const hay = `${c.title} ${c.summary} ${(c.keywords || []).join(' ')}`;
    const hayNorm = normalize(hay);
    let score = 0;
    // token match simple
    const tokens = qNorm.split(/\s+/).filter(Boolean);
    tokens.forEach((t) => {
        if (t.length < 3) return;
        if (hayNorm.includes(t)) score += 2;
    });
    // direct keyword boost
    (c.keywords || []).forEach((k) => {
        const kn = normalize(k);
        if (kn && qNorm.includes(kn)) score += 6;
    });
    return score;
}

function pickBestCase(question) {
    const qNorm = normalize(question);
    const scored = OFFLINE_CASES.map((c) => ({ c, s: scoreCase(qNorm, c) }))
        .sort((a, b) => b.s - a.s);
    const best = scored[0];
    if (!best || best.s <= 0) return null;
    // If ambiguity, we can ask user to choose later; for now return best.
    return best.c;
}

function formatOutput(output) {
    // Compact, readable. Keep links at the end.
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

/**
 * Estado de una conversación offline.
 */
export class OfflineSession {
    constructor() {
        /** @type {OfflineCase | null} */
        this.activeCase = null;
        /** @type {number} */
        this.questionIdx = 0;
        /** @type {Record<string, string>} */
        this.answers = {};
    }

    reset() {
        this.activeCase = null;
        this.questionIdx = 0;
        this.answers = {};
    }

    /**
     * @param {string} userText
     * @returns {{ type: 'answer', text: string, chips: string[] } | { type: 'ask', text: string, chips: string[] } | { type: 'error', text: string, chips: string[] }}
     */
    handleUserMessage(userText) {
        const q = (userText || '').trim();
        if (!q) {
            return { type: 'error', text: 'Escribe una pregunta o elige una opción.', chips: [] };
        }

        // If we are in a wizard question, try to map to an option.
        if (this.activeCase && this.activeCase.questions?.length && this.questionIdx < this.activeCase.questions.length) {
            const current = this.activeCase.questions[this.questionIdx];
            const picked = this.pickOptionFromText(current, q);
            if (!picked) {
                return {
                    type: 'ask',
                    text: `${current.prompt} (elige una opción)`,
                    chips: current.options.map((o) => o.label),
                };
            }
            this.answers[current.id] = picked.id;
            this.questionIdx += 1;
        } else if (!this.activeCase) {
            this.activeCase = pickBestCase(q);
            this.questionIdx = 0;
            this.answers = {};
            if (!this.activeCase) {
                return {
                    type: 'answer',
                    text: 'Modo offline (limitado): no encontré un caso precargado para esa pregunta. Conéctate a Internet para consultarlo en modo online.',
                    chips: ['¿Qué sí puedes responder offline?', 'Glosario básico', 'Cómo ubicar la autoridad competente'],
                };
            }
        }

        // If there are still wizard questions, ask next.
        if (this.activeCase.questions?.length && this.questionIdx < this.activeCase.questions.length) {
            const next = this.activeCase.questions[this.questionIdx];
            return { type: 'ask', text: next.prompt, chips: next.options.map((o) => o.label) };
        }

        // Otherwise, answer with the case output.
        const out = this.activeCase.output;
        const text = formatOutput(out);
        const chips = (this.activeCase.suggested_questions || []).slice(0, 6);
        return { type: 'answer', text, chips };
    }

    /**
     * @param {OfflineQuestion} q
     * @param {string} text
     * @returns {OfflineOption|null}
     */
    pickOptionFromText(q, text) {
        const t = normalize(text);
        // match by label or keywords
        for (const opt of q.options) {
            const label = normalize(opt.label);
            if (label && t === label) return opt;
        }
        for (const opt of q.options) {
            const label = normalize(opt.label);
            if (label && t.includes(label)) return opt;
            const kws = opt.keywords || [];
            for (const kw of kws) {
                const kn = normalize(kw);
                if (kn && t.includes(kn)) return opt;
            }
        }
        return null;
    }
}

export function getOfflineCasesCount() {
    return OFFLINE_CASES.length;
}

