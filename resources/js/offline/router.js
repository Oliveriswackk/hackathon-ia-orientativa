import { OFFLINE_CONFIDENT_THRESHOLD, MIN_OFFLINE_SCORE } from './matcher.js';

/**
 * @param {{ online: boolean, score: number, preferOfflineWhenConfident?: boolean }} p
 * @returns {'online'|'offline'|'offline_low_confidence'}
 */
export function decideRoute(p) {
    const { online, score, preferOfflineWhenConfident } = p;
    if (!online) {
        return score >= MIN_OFFLINE_SCORE ? 'offline' : 'offline_low_confidence';
    }
    if (preferOfflineWhenConfident && score >= OFFLINE_CONFIDENT_THRESHOLD) {
        return 'offline';
    }
    return 'online';
}
