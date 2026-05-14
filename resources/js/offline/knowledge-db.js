const DB_NAME = 'electoral-orientacion';
const DB_VERSION = 1;

/**
 * @returns {Promise<IDBDatabase>}
 */
export function openKnowledgeDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onerror = () => reject(req.error);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains('meta')) {
                db.createObjectStore('meta');
            }
            if (!db.objectStoreNames.contains('intents')) {
                db.createObjectStore('intents', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('entries')) {
                db.createObjectStore('entries', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('synonyms')) {
                db.createObjectStore('synonyms', { keyPath: 'from' });
            }
            if (!db.objectStoreNames.contains('rulePacks')) {
                db.createObjectStore('rulePacks', { keyPath: 'id' });
            }
        };
        req.onsuccess = () => resolve(req.result);
    });
}

/**
 * @param {IDBDatabase} db
 * @param {import('./catalog-types.js').FullCatalog} data
 */
export async function putFullCatalog(db, data) {
    const stores = ['intents', 'entries', 'synonyms', 'rulePacks'];
    await new Promise((resolve, reject) => {
        const tx = db.transaction([...stores, 'meta'], 'readwrite');
        tx.onerror = () => reject(tx.error);
        tx.oncomplete = () => resolve();

        for (const name of stores) {
            const st = tx.objectStore(name);
            st.clear();
        }

        for (const intent of data.intents || []) {
            tx.objectStore('intents').put(intent);
        }
        for (const entry of data.entries || []) {
            tx.objectStore('entries').put(entry);
        }
        for (const row of data.synonyms || []) {
            if (row.from != null) tx.objectStore('synonyms').put(row);
        }
        for (const rp of data.rulePacks || []) {
            if (rp.id) tx.objectStore('rulePacks').put(rp);
        }
        tx.objectStore('meta').put(data.version ?? '0', 'catalogVersion');
    });
}

/**
 * @param {IDBDatabase} db
 * @returns {Promise<import('./catalog-types.js').FullCatalog | null>}
 */
export async function getFullCatalog(db) {
    const version = await new Promise((resolve, reject) => {
        const tx = db.transaction('meta', 'readonly');
        const r = tx.objectStore('meta').get('catalogVersion');
        r.onsuccess = () => resolve(r.result ?? null);
        r.onerror = () => reject(r.error);
    });
    if (version == null) return null;

    const readAll = (storeName) =>
        new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const r = tx.objectStore(storeName).getAll();
            r.onsuccess = () => resolve(r.result || []);
            r.onerror = () => reject(r.error);
        });

    const [intents, entries, synonyms, rulePacks] = await Promise.all([
        readAll('intents'),
        readAll('entries'),
        readAll('synonyms'),
        readAll('rulePacks'),
    ]);

    return {
        version: String(version),
        intents,
        entries,
        synonyms,
        rulePacks,
    };
}
