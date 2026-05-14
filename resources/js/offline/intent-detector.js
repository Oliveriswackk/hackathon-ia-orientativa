/**
 * @typedef {{ id: string, label?: string, priority?: number, keywords?: string[], aliases?: string[] }} CatalogIntent
 */

import { normalize } from './text-normalize.js';

/**
 * @param {string} queryRaw
 * @param {CatalogIntent[]} intents
 * @returns {{ intentId: string | null, confidence: number, signals: string[] }}
 */
export function detectIntent(queryRaw, intents) {
    const q = normalize(queryRaw || '');
    if (!q || !intents?.length) {
        return { intentId: null, confidence: 0, signals: [] };
    }

    let bestId = null;
    let bestScore = 0;
    const signals = [];

    for (const intent of intents) {
        const priority = intent.priority ?? 0;
        let s = 0;
        const terms = [...(intent.keywords || []), ...(intent.aliases || [])];
        for (const term of terms) {
            const tn = normalize(term);
            if (!tn) continue;
            if (q.includes(tn)) {
                s += 5 + tn.length * 0.1;
                signals.push(`${intent.id}:${tn}`);
            }
        }
        s += priority * 0.01;
        if (s > bestScore) {
            bestScore = s;
            bestId = intent.id;
        }
    }

    const confidence = Math.min(1, bestScore / 15);
    return { intentId: bestScore > 0 ? bestId : null, confidence, signals };
}
