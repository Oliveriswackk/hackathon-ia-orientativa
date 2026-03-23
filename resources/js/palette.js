/**
 * Paletas de color: aplica variables en :root, persiste en localStorage y panel "Personalizada".
 */

const STORAGE_PALETTE = 'ai_palette';
const STORAGE_CUSTOM = 'ai_custom_palette';

/** @type {Record<string, { label: string, vars: Record<string, string> }> | null} */
let metaCache = null;

function readMetaFromDom() {
    if (metaCache) {
        return metaCache;
    }
    const el = document.getElementById('ai-palette-meta-data');
    if (!el) {
        return {};
    }
    try {
        metaCache = JSON.parse(el.textContent || '{}');
        return metaCache;
    } catch {
        return {};
    }
}

/**
 * Aplica un mapa de variables CSS en el elemento raíz.
 * @param {Record<string, string>} vars
 */
export function applyCssVariables(vars) {
    const root = document.documentElement.style;
    Object.entries(vars).forEach(([key, value]) => {
        if (key.startsWith('--') && value) {
            root.setProperty(key, value);
        }
    });
    document.dispatchEvent(new CustomEvent('ai-palette-applied', { detail: { vars } }));
}

function hexToRgb(hex) {
    const h = hex.replace('#', '').trim();
    const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
    const n = parseInt(full, 16);
    if (Number.isNaN(n)) {
        return { r: 0, g: 0, b: 0 };
    }
    return {
        r: (n >> 16) & 255,
        g: (n >> 8) & 255,
        b: n & 255,
    };
}

function rgbToHex(r, g, b) {
    const to = (v) => v.toString(16).padStart(2, '0');
    return `#${to(r)}${to(g)}${to(b)}`;
}

function mixRgb(c1, c2, t) {
    const a = hexToRgb(c1);
    const b = hexToRgb(c2);
    const r = Math.round(a.r + (b.r - a.r) * t);
    const g = Math.round(a.g + (b.g - a.g) * t);
    const bl = Math.round(a.b + (b.b - a.b) * t);
    return rgbToHex(r, g, bl);
}

/** Luminancia relativa WCAG (0–1). */
function relativeLuminance(hex) {
    const { r, g, b } = hexToRgb(hex);
    const lin = [r, g, b].map((v) => {
        const c = v / 255;
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * lin[0] + 0.7152 * lin[1] + 0.0722 * lin[2];
}

/**
 * Superficie derivada para paleta personalizada (sin picker extra).
 */
function deriveSurface(bgHex, textHex) {
    const L = relativeLuminance(bgHex);
    if (L < 0.12) {
        return mixRgb(bgHex, textHex, 0.18);
    }
    return mixRgb(bgHex, '#ffffff', 0.35);
}

/**
 * Texto sobre botón primario según contraste aproximado.
 */
function deriveOnPrimary(primaryHex, textHex, bgHex) {
    const L = relativeLuminance(primaryHex);
    if (L > 0.55) {
        return mixRgb(primaryHex, textHex, 0.92);
    }
    return mixRgb(primaryHex, bgHex, 0.88);
}

function readCustomFromStorage() {
    try {
        const raw = localStorage.getItem(STORAGE_CUSTOM);
        if (!raw) {
            return null;
        }
        const o = JSON.parse(raw);
        return typeof o === 'object' && o !== null ? o : null;
    } catch {
        return null;
    }
}

function writeCustomToStorage(vars) {
    localStorage.setItem(STORAGE_CUSTOM, JSON.stringify(vars));
}

/**
 * Aplica preset por id (koi, blood_water, …) o personalizada desde localStorage.
 */
export function applyStoredPalette() {
    const meta = readMetaFromDom();
    const id = localStorage.getItem(STORAGE_PALETTE) || 'koi';

    if (id === 'custom') {
        const custom = readCustomFromStorage();
        if (custom && Object.keys(custom).length) {
            applyCssVariables(custom);
            return { id: 'custom', vars: custom };
        }
    }

    const preset = meta[id]?.vars;
    if (preset) {
        applyCssVariables(preset);
        return { id, vars: preset };
    }

    const fallback = meta.koi?.vars;
    if (fallback) {
        applyCssVariables(fallback);
        return { id: 'koi', vars: fallback };
    }

    return { id: 'koi', vars: {} };
}

/**
 * Construye objeto completo de variables para "Personalizada" a partir de los 5 colores base.
 */
function buildCustomVarsFromPickers() {
    const get = (id) => document.getElementById(id)?.value;
    const primary = get('ai-custom-primary');
    const secondary = get('ai-custom-secondary');
    const accent = get('ai-custom-accent');
    const bg = get('ai-custom-bg');
    const text = get('ai-custom-text');
    if (!primary || !secondary || !accent || !bg || !text) {
        return null;
    }
    const surface = deriveSurface(bg, text);
    const onPrimary = deriveOnPrimary(primary, text, bg);
    return {
        '--color-primary': primary,
        '--color-secondary': secondary,
        '--color-accent': accent,
        '--color-bg': bg,
        '--color-text': text,
        '--color-surface': surface,
        '--color-on-primary': onPrimary,
    };
}

function fillCustomPickersFromVars(vars) {
    const map = [
        ['ai-custom-primary', '--color-primary'],
        ['ai-custom-secondary', '--color-secondary'],
        ['ai-custom-accent', '--color-accent'],
        ['ai-custom-bg', '--color-bg'],
        ['ai-custom-text', '--color-text'],
    ];
    map.forEach(([inputId, key]) => {
        const el = document.getElementById(inputId);
        if (el && vars[key]) {
            el.value = vars[key];
        }
    });
}

function wirePaletteUi() {
    const select = document.getElementById('ai-palette-select');
    const customPanel = document.getElementById('ai-palette-custom');
    const btnApply = document.getElementById('ai-palette-custom-apply');
    const btnReset = document.getElementById('ai-palette-custom-reset');
    if (!select) {
        return;
    }

    const meta = readMetaFromDom();
    const stored = localStorage.getItem(STORAGE_PALETTE) || 'koi';
    select.value = meta[stored] ? stored : 'koi';

    const syncCustomPanel = () => {
        const open = select.value === 'custom';
        if (customPanel) {
            customPanel.dataset.open = open ? 'true' : 'false';
        }
        if (open) {
            const c = readCustomFromStorage();
            const base = c || meta.koi?.vars || {};
            fillCustomPickersFromVars(base);
        }
    };

    syncCustomPanel();

    select.addEventListener('change', () => {
        const id = select.value;
        localStorage.setItem(STORAGE_PALETTE, id);
        if (id === 'custom') {
            syncCustomPanel();
            const c = readCustomFromStorage();
            if (c) {
                applyCssVariables(c);
            } else {
                fillCustomPickersFromVars(meta.koi?.vars || {});
            }
        } else {
            const vars = meta[id]?.vars;
            if (vars) {
                applyCssVariables(vars);
            }
            syncCustomPanel();
        }
    });

    btnApply?.addEventListener('click', () => {
        const built = buildCustomVarsFromPickers();
        if (!built) {
            return;
        }
        writeCustomToStorage(built);
        localStorage.setItem(STORAGE_PALETTE, 'custom');
        select.value = 'custom';
        applyCssVariables(built);
        syncCustomPanel();
    });

    btnReset?.addEventListener('click', () => {
        localStorage.removeItem(STORAGE_CUSTOM);
        const koi = meta.koi?.vars;
        if (koi) {
            fillCustomPickersFromVars(koi);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    applyStoredPalette();
    wirePaletteUi();
});
