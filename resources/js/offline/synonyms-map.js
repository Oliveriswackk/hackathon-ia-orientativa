import { normalize } from './text-normalize.js';

/**
 * @param {{ from?: string, to?: string }[]} rows
 * @returns {Record<string, string>}
 */
export function synonymRowsToMap(rows) {
    /** @type {Record<string, string>} */
    const m = {};
    for (const row of rows || []) {
        if (row.from != null && row.to != null) {
            m[normalize(String(row.from))] = String(row.to);
        }
    }
    return m;
}
