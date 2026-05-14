export { OfflineSession } from './offline-session.js';
export { ensureCatalogLoaded, getOfflineCasesCount, clearCatalogCache } from './catalog-loader.js';
export { decideRoute } from './router.js';
export { matchQuerySync, MIN_OFFLINE_SCORE, OFFLINE_CONFIDENT_THRESHOLD, SCORE_WEIGHTS } from './matcher.js';
export { synonymRowsToMap } from './synonyms-map.js';
