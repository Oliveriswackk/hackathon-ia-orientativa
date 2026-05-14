/**
 * Scoring determinista: keywords, tokens, doc_type/proceso, intent lock.
 */

import { detectIntent } from './intent-detector.js';
import { MIN_TOKEN_LEN, applySynonyms, normalize, tokenize } from './text-normalize.js';

/** Score mínimo para considerar que hubo match usable (ajustable). */
export const MIN_OFFLINE_SCORE = 2;

/** Si el score offline supera esto, decideRoute puede preferir catálogo en modo híbrido. */
export const OFFLINE_CONFIDENT_THRESHOLD = 10;

export const SCORE_WEIGHTS = {
    tokenOverlap: 2,
    keyword: 6,
    docTypeKeyword: 8,
    procesoKeyword: 8,
    intentMatch: 4,
    titleStartBonus: 3,
    fuzzyKeyword: 2,
};

/**
 * @param {string} a
 * @param {string} b
 * @returns {number}
 */
function levenshtein(a, b) {
    if (a === b) return 0;
    const m = a.length;
    const n = b.length;
    if (m === 0) return n;
    if (n === 0) return m;
    if (m > 15 || n > 15) return 99;
    const dp = Array.from({ length: m + 1 }, () => new Array(n + 1).fill(0));
    for (let i = 0; i <= m; i++) dp[i][0] = i;
    for (let j = 0; j <= n; j++) dp[0][j] = j;
    for (let i = 1; i <= m; i++) {
        for (let j = 1; j <= n; j++) {
            const cost = a[i - 1] === b[j - 1] ? 0 : 1;
            dp[i][j] = Math.min(dp[i - 1][j] + 1, dp[i][j - 1] + 1, dp[i - 1][j - 1] + cost);
        }
    }
    return dp[m][n];
}

/**
 * @param {import('./types.js').CatalogEntry} entry
 * @param {string} qNorm
 * @param {{ intentId?: string | null, synonymMap?: Record<string, string> }} ctx
 * @returns {number}
 */
export function scoreEntry(entry, qNorm, ctx = {}) {
    const synonymMap = ctx.synonymMap || {};
    const query = applySynonyms(qNorm, synonymMap);

    const hay = [
        entry.title,
        entry.summary,
        (entry.keywords || []).join(' '),
        entry.searchText || '',
        entry.doc_type || '',
        entry.proceso || '',
    ].join(' ');
    const hayNorm = normalize(hay);

    let score = 0;
    const tokens = tokenize(query);

    for (const t of tokens) {
        if (hayNorm.includes(t)) {
            score += SCORE_WEIGHTS.tokenOverlap;
        }
        const titleN = normalize(entry.title || '');
        if (titleN.startsWith(t) || titleN.includes(` ${t}`)) {
            score += SCORE_WEIGHTS.titleStartBonus;
        }
    }

    for (const k of entry.keywords || []) {
        const kn = normalize(k);
        if (kn && query.includes(kn)) {
            score += SCORE_WEIGHTS.keyword;
        }
        if (kn.length >= 5 && query.length >= 5) {
            for (const tok of tokens) {
                if (tok.length >= 5 && levenshtein(tok, kn) <= 1) {
                    score += SCORE_WEIGHTS.fuzzyKeyword;
                    break;
                }
            }
        }
    }

    if (entry.doc_type) {
        const dtn = normalize(entry.doc_type.replace(/_/g, ' '));
        if (dtn && query.includes(dtn)) score += SCORE_WEIGHTS.docTypeKeyword;
        if (query.includes(normalize(entry.doc_type))) score += SCORE_WEIGHTS.docTypeKeyword;
    }
    if (entry.proceso) {
        const pn = normalize(entry.proceso.replace(/_/g, ' '));
        if (pn && query.includes(pn)) score += SCORE_WEIGHTS.procesoKeyword;
    }

    if (ctx.intentId && entry.intentId === ctx.intentId) {
        score += SCORE_WEIGHTS.intentMatch;
    }

    return score;
}

/**
 * @param {import('./types.js').CatalogEntry[]} entries
 * @param {string} question
 * @param {{ intentId?: string | null, synonymMap?: Record<string, string> }} ctx
 * @returns {{ entry: import('./types.js').CatalogEntry | null, score: number, ranked: { entry: import('./types.js').CatalogEntry, score: number }[] }}
 */
export function pickBestEntry(entries, question, ctx = {}) {
    const qNorm = normalize(question);
    const ranked = (entries || [])
        .map((entry) => ({ entry, score: scoreEntry(entry, qNorm, ctx) }))
        .sort((a, b) => b.score - a.score);

    const best = ranked[0];
    if (!best || best.score < MIN_OFFLINE_SCORE) {
        return { entry: null, score: 0, ranked };
    }
    return { entry: best.entry, score: best.score, ranked };
}

/**
 * @param {string} question
 * @param {import('./types.js').CatalogEntry[]} entries
 * @param {import('./intent-detector.js').CatalogIntent[]} intents
 * @param {Record<string, string>} [synonymMap]
 */
export function matchQuerySync(question, entries, intents, synonymMap = {}) {
    const det = detectIntent(question, intents || []);
    const ctx = { intentId: det.intentId, synonymMap };
    return { ...pickBestEntry(entries, question, ctx), intent: det };
}
