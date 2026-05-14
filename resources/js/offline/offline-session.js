import { composeGuidanceTurn } from './guidance-composer.js';
import { matchQuerySync } from './matcher.js';
import { normalize } from './text-normalize.js';
import { formatOutput } from './format-output.js';
import { synonymRowsToMap } from './synonyms-map.js';

/**
 * @typedef {import('./types.js').CatalogEntry} CatalogEntry
 * @typedef {import('./types.js').OfflineQuestion} OfflineQuestion
 * @typedef {import('./types.js').OfflineOption} OfflineOption
 * @typedef {import('./catalog-types.js').FullCatalog} FullCatalog
 */

export class OfflineSession {
    /**
     * @param {FullCatalog} catalog
     */
    constructor(catalog) {
        this.catalog = catalog;
        this.entries = catalog.entries || [];
        this.intents = catalog.intents || [];
        this.synonymMap = synonymRowsToMap(catalog.synonyms);
        /** @type {CatalogEntry | null} */
        this.activeCase = null;
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
     * @returns {{ type: 'answer'|'ask'|'error', text: string, chips: string[], guidance?: object }}
     */
    handleUserMessage(userText) {
        const q = (userText || '').trim();
        if (!q) {
            return { type: 'error', text: 'Escribe una pregunta o elige una opción.', chips: [] };
        }

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
            const { entry } = matchQuerySync(q, this.entries, this.intents, this.synonymMap);
            this.activeCase = entry;
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

        if (this.activeCase.questions?.length && this.questionIdx < this.activeCase.questions.length) {
            const next = this.activeCase.questions[this.questionIdx];
            return { type: 'ask', text: next.prompt, chips: next.options.map((o) => o.label) };
        }

        return this.renderFinalAnswer();
    }

    /**
     * @returns {{ type: 'answer', text: string, chips: string[], guidance?: object }}
     */
    renderFinalAnswer() {
        if (!this.activeCase) {
            return { type: 'error', text: 'Sin caso activo.', chips: [] };
        }
        const out = this.activeCase.output;
        let text = formatOutput(out);
        /** @type {object|undefined} */
        let guidance;
        if (this.activeCase.rule_pack || this.activeCase.rule_pack_id) {
            const pack =
                this.activeCase.rule_pack ||
                (this.catalog.rulePacks || []).find((p) => p.id === this.activeCase.rule_pack_id);
            if (pack) {
                guidance = composeGuidanceTurn(pack, this.activeCase, this.answers, {});
                text += '\n\n--- Orientación estructurada (offline) ---\n';
                text += JSON.stringify(guidance, null, 2);
            }
        }

        const chips = (this.activeCase.suggested_questions || []).slice(0, 6);
        return { type: 'answer', text, chips, guidance };
    }

    /**
     * @param {OfflineQuestion} question
     * @param {string} text
     * @returns {OfflineOption|null}
     */
    pickOptionFromText(question, text) {
        const t = normalize(text);
        for (const opt of question.options) {
            const label = normalize(opt.label);
            if (label && t === label) return opt;
        }
        for (const opt of question.options) {
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

    /**
     * Respuesta final sin wizard (p. ej. columna Groq con alta confianza offline).
     * @param {CatalogEntry} entry
     */
    buildFinalAnswerForEntry(entry) {
        this.activeCase = entry;
        this.questionIdx = entry.questions?.length ?? 0;
        this.answers = {};
        return this.renderFinalAnswer();
    }
}
