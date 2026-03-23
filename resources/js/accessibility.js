/**
 * Modo accesible: contraste WCAG AA sobre la paleta actual, preferencia en localStorage y reglas CSS extra.
 */

import { applyStoredPalette } from './palette';

const STORAGE_ACCESSIBLE = 'ai_accessible';

/** @typedef {{ r: number, g: number, b: number }} RGB */

/**
 * @param {string} raw
 * @returns {RGB | null}
 */
function parseCssColor(raw) {
    if (!raw || raw === 'transparent') {
        return null;
    }
    const s = raw.trim();
    if (s.startsWith('#')) {
        const h = s.slice(1);
        const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
        const n = parseInt(full, 16);
        if (Number.isNaN(n)) {
            return null;
        }
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
    }
    const m = s.match(/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)/i);
    if (m) {
        return {
            r: Math.round(Number(m[1])),
            g: Math.round(Number(m[2])),
            b: Math.round(Number(m[3])),
        };
    }
    return null;
}

/**
 * @param {RGB} c
 */
function luminance(c) {
    const lin = [c.r, c.g, c.b].map((v) => {
        const x = v / 255;
        return x <= 0.03928 ? x / 12.92 : ((x + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * lin[0] + 0.7152 * lin[1] + 0.0722 * lin[2];
}

/**
 * Contraste WCAG entre dos luminancias (4.5 = AA texto normal).
 */
function contrastRatioLum(l1, l2) {
    const L1 = Math.max(l1, l2);
    const L2 = Math.min(l1, l2);
    return (L1 + 0.05) / (L2 + 0.05);
}

/**
 * @param {RGB} fg
 * @param {RGB} bg
 * @param {number} minRatio
 */
function ensureContrastRgb(fg, bg, minRatio = 4.5) {
    const Lbg = luminance(bg);
    const toward = Lbg > 0.179 ? { r: 0, g: 0, b: 0 } : { r: 255, g: 255, b: 255 };
    let cur = { ...fg };
    for (let i = 0; i < 48; i++) {
        const r = contrastRatioLum(luminance(cur), Lbg);
        if (r >= minRatio) {
            return cur;
        }
        cur = {
            r: Math.round(cur.r + (toward.r - cur.r) * 0.1),
            g: Math.round(cur.g + (toward.g - cur.g) * 0.1),
            b: Math.round(cur.b + (toward.b - cur.b) * 0.1),
        };
    }
    return cur;
}

/**
 * @param {RGB} c
 */
function rgbToCss(c) {
    return `rgb(${c.r}, ${c.g}, ${c.b})`;
}

/**
 * Lee color resuelto de una variable en :root (tras color-mix puede ser rgb).
 * @param {string} varName
 */
function readVarColor(varName) {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    if (!raw || raw.startsWith('var(')) {
        return null;
    }
    return parseCssColor(raw);
}

/**
 * Ajusta variables clave para cumplir al menos 4.5:1 donde aplica.
 */
export function applyAccessibleContrast() {
    if (document.documentElement.getAttribute('data-ai-accessible') !== 'true') {
        return;
    }

    const bg = readVarColor('--color-bg');
    const surface = readVarColor('--color-surface');
    const text = readVarColor('--color-text');
    const primary = readVarColor('--color-primary');
    const onPrimary = readVarColor('--color-on-primary');

    if (!bg || !text) {
        return;
    }

    /** Primero el color primario sobre el fondo (enlaces, logo); luego texto sobre fondo/superficie; luego texto sobre botón. */
    let primaryAdj = primary ? ensureContrastRgb({ ...primary }, bg, 4.5) : null;
    if (primaryAdj) {
        document.documentElement.style.setProperty('--color-primary', rgbToCss(primaryAdj));
    }

    if (onPrimary && primaryAdj) {
        const onAdj = ensureContrastRgb({ ...onPrimary }, primaryAdj, 4.5);
        document.documentElement.style.setProperty('--color-on-primary', rgbToCss(onAdj));
    }

    let textAdj = { ...text };
    textAdj = ensureContrastRgb(textAdj, bg, 4.5);
    if (surface) {
        textAdj = ensureContrastRgb(textAdj, surface, 4.5);
    }
    document.documentElement.style.setProperty('--color-text', rgbToCss(textAdj));
}

function isAccessibleEnabled() {
    return localStorage.getItem(STORAGE_ACCESSIBLE) === '1';
}

function setAccessibleEnabled(on) {
    if (on) {
        localStorage.setItem(STORAGE_ACCESSIBLE, '1');
    } else {
        localStorage.removeItem(STORAGE_ACCESSIBLE);
    }
}

function syncToggleUi(btn) {
    if (!btn) {
        return;
    }
    const on = document.documentElement.getAttribute('data-ai-accessible') === 'true';
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    const label = on ? 'Desactivar modo accesible' : 'Activar modo accesible (contraste y enlaces)';
    btn.setAttribute('aria-label', label);
    const text = btn.querySelector('.ai-accessible-toggle-text');
    if (text) {
        text.textContent = on ? 'Modo accesible: activo' : 'Modo accesible';
    }
}

// Registro temprano: el módulo palette emite al aplicar variables antes/después de nuestro DOMContentLoaded.
document.addEventListener('ai-palette-applied', () => {
    if (document.documentElement.getAttribute('data-ai-accessible') === 'true') {
        applyAccessibleContrast();
    }
});

function initAccessibility() {
    const btn = document.getElementById('ai-accessible-toggle');
    if (isAccessibleEnabled()) {
        document.documentElement.setAttribute('data-ai-accessible', 'true');
    } else {
        document.documentElement.removeAttribute('data-ai-accessible');
    }

    if (document.documentElement.getAttribute('data-ai-accessible') === 'true') {
        applyAccessibleContrast();
    }

    syncToggleUi(btn);

    btn?.addEventListener('click', () => {
        const now = document.documentElement.getAttribute('data-ai-accessible') === 'true';
        if (now) {
            document.documentElement.removeAttribute('data-ai-accessible');
            setAccessibleEnabled(false);
            applyStoredPalette();
        } else {
            document.documentElement.setAttribute('data-ai-accessible', 'true');
            setAccessibleEnabled(true);
            applyAccessibleContrast();
        }
        syncToggleUi(btn);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initAccessibility();
});
