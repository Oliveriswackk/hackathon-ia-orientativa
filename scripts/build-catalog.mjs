import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');

const intentsPath = path.join(root, 'resources/data/offline/intents.json');
const synonymsPath = path.join(root, 'resources/data/offline/synonyms.json');
const casesDir = path.join(root, 'resources/data/offline/cases');
const rulePacksDir = path.join(root, 'resources/data/offline/rule-packs');
const outPath = path.join(root, 'public/catalog.json');

const intents = JSON.parse(fs.readFileSync(intentsPath, 'utf8'));
const synonyms = JSON.parse(fs.readFileSync(synonymsPath, 'utf8'));

const entries = fs
    .readdirSync(casesDir)
    .filter((f) => f.endsWith('.json'))
    .map((f) => JSON.parse(fs.readFileSync(path.join(casesDir, f), 'utf8')));

let rulePacks = [];
if (fs.existsSync(rulePacksDir)) {
    rulePacks = fs
        .readdirSync(rulePacksDir)
        .filter((f) => f.endsWith('.json'))
        .map((f) => {
            const o = JSON.parse(fs.readFileSync(path.join(rulePacksDir, f), 'utf8'));
            if (!o.id) {
                o.id = path.basename(f, '.json');
            }
            return o;
        });
}

const packById = Object.fromEntries(rulePacks.map((p) => [p.id, p]));
for (const e of entries) {
    if (e.rule_pack_id && packById[e.rule_pack_id]) {
        e.rule_pack = packById[e.rule_pack_id];
    }
}

const catalog = {
    version: '1.0.0',
    intents: intents.intents ?? intents,
    synonyms: synonyms.synonyms ?? synonyms,
    entries,
    rulePacks,
};

fs.mkdirSync(path.dirname(outPath), { recursive: true });
fs.writeFileSync(outPath, JSON.stringify(catalog, null, 2), 'utf8');
console.log('Wrote', outPath, 'entries:', entries.length, 'rulePacks:', rulePacks.length);
