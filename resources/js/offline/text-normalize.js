/** Longitud mínima de token para contar en overlap (evita ruido: "el", "un"). */
export const MIN_TOKEN_LEN = 3;

/** Lista corta de stopwords en español (sin acentos, post-normalize). */
export const STOPWORDS = new Set([
    'que',
    'con',
    'por',
    'una',
    'uno',
    'del',
    'los',
    'las',
    'como',
    'para',
    'hay',
    'fue',
    'son',
    'esta',
    'este',
    'pero',
    'mas',
    'muy',
    'sobre',
]);

/**
 * @param {string} str
 * @returns {string}
 */
export function normalize(str) {
    return (str || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^\p{L}\p{N}\s]/gu, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * @param {string} normalized
 * @returns {string[]}
 */
export function tokenize(normalized) {
    return (normalized || '')
        .split(/\s+/)
        .filter((t) => t.length >= MIN_TOKEN_LEN && !STOPWORDS.has(t));
}

/**
 * Aplica mapa de sinónimos (from → to) sobre texto ya normalizado.
 * @param {string} normalized
 * @param {Record<string, string>} synonymMap
 */
export function applySynonyms(normalized, synonymMap) {
    if (!synonymMap || !normalized) return normalized;
    let out = normalized;
    for (const [from, to] of Object.entries(synonymMap)) {
        const f = from.toLowerCase().trim();
        if (!f) continue;
        const re = new RegExp(`\\b${escapeRegExp(f)}\\b`, 'g');
        out = out.replace(re, to);
    }
    return out;
}

function escapeRegExp(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
