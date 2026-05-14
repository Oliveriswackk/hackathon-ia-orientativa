import intentsFallback from '../../data/offline/intents.json';
import synonymsFallback from '../../data/offline/synonyms.json';
import { getFullCatalog, openKnowledgeDb, putFullCatalog } from './knowledge-db.js';

/** @type {import('./catalog-types.js').FullCatalog | null} */
let cachedCatalog = null;

/** @type {Record<string, { default: object }>} */
const CASE_MODULES = import.meta.glob('../../data/offline/cases/*.json', { eager: true });
/** @type {Record<string, { default: object }>} */
const RULE_PACK_MODULES = import.meta.glob('../../data/offline/rule-packs/*.json', { eager: true });

/**
 * @param {import('./types.js').CatalogEntry[]} entries
 * @param {import('./types.js').RulePack[]} rulePacks
 */
function attachRulePacks(entries, rulePacks) {
    const byId = Object.fromEntries((rulePacks || []).filter((p) => p.id).map((p) => [p.id, p]));
    return entries.map((e) => {
        if (e.rule_pack_id && byId[e.rule_pack_id]) {
            return { ...e, rule_pack: byId[e.rule_pack_id] };
        }
        return { ...e };
    });
}

function buildFallbackCatalog() {
    const rawEntries = Object.values(CASE_MODULES).map((m) => /** @type {import('./types.js').CatalogEntry} */ (m.default));
    const rulePacks = Object.values(RULE_PACK_MODULES).map((m) => {
        const p = /** @type {import('./types.js').RulePack} */ (m.default);
        if (!p.id && p.doc_type) {
            p.id = p.doc_type + (p.proceso ? `_${p.proceso}` : '');
        }
        return p;
    });
    const intents = intentsFallback.intents ?? intentsFallback;
    const synonyms = synonymsFallback.synonyms ?? synonymsFallback;
    const entries = attachRulePacks(rawEntries, rulePacks);
    return {
        version: 'embedded-1',
        intents: Array.isArray(intents) ? intents : [],
        synonyms: Array.isArray(synonyms) ? synonyms : [],
        entries,
        rulePacks,
    };
}

/**
 * Carga catálogo: IndexedDB si coincide versión con remoto, si no fetch `/catalog.json`, si falla embebido Vite.
 * @returns {Promise<import('./catalog-types.js').FullCatalog>}
 */
export async function ensureCatalogLoaded() {
    if (cachedCatalog) {
        return cachedCatalog;
    }

    let remote = null;
    try {
        const res = await fetch(`${import.meta.env.BASE_URL || '/'}catalog.json`, {
            cache: 'no-cache',
        });
        if (res.ok) {
            remote = await res.json();
        }
    } catch {
        remote = null;
    }

    if (!remote || !remote.entries?.length) {
        cachedCatalog = buildFallbackCatalog();
        return cachedCatalog;
    }

    try {
        const db = await openKnowledgeDb();
        const local = await getFullCatalog(db);
        if (local && local.version === remote.version && local.entries?.length) {
            cachedCatalog = {
                ...local,
                entries: attachRulePacks(local.entries, local.rulePacks || []),
            };
            return cachedCatalog;
        }
        await putFullCatalog(db, remote);
        cachedCatalog = {
            ...remote,
            entries: attachRulePacks(remote.entries, remote.rulePacks || []),
        };
        return cachedCatalog;
    } catch {
        cachedCatalog = {
            ...remote,
            entries: attachRulePacks(remote.entries, remote.rulePacks || []),
        };
        return cachedCatalog;
    }
}

/**
 * @returns {number}
 */
export function getOfflineCasesCount() {
    return cachedCatalog?.entries?.length ?? Object.keys(CASE_MODULES).length;
}

export function clearCatalogCache() {
    cachedCatalog = null;
}
