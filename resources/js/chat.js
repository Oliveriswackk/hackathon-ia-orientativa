/**
 * Comparador dual Groq / Ollama: historiales independientes, chips de seguimiento y estado de conexión.
 */

const SYSTEM_PROMPT =
    'Eres un orientador electoral para México (público joven). Responde en español, con claridad. ' +
    'No inventes plazos ni datos normativos; si no estás seguro, dilo. Solo temas electorales y ciudadanía.';

/**
 * @param {string} str
 */
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

/**
 * @param {string} apiBase
 * @param {string} question
 * @param {'groq'|'ollama'} provider
 */
async function postAsk(apiBase, question, provider) {
    const url = `${apiBase.replace(/\/$/, '')}/ai/ask`;
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify({
            question,
            system_prompt: SYSTEM_PROMPT,
            provider,
        }),
    });
    const json = await res.json();
    return { ok: res.ok, status: res.status, json };
}

/**
 * @param {string} apiBase
 */
async function fetchStatus(apiBase) {
    const url = `${apiBase.replace(/\/$/, '')}/ai/status`;
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) {
        return null;
    }
    return res.json();
}

/**
 * @param {HTMLElement} statusEl
 * @param {boolean} connected
 * @param {string} okText
 * @param {string} badText
 */
function renderConnectionStatus(statusEl, connected, okText, badText) {
    if (!statusEl) {
        return;
    }
    statusEl.dataset.connected = connected ? 'true' : 'false';
    const label = statusEl.querySelector('.dual-ia-status-label');
    if (label) {
        label.textContent = connected ? okText : badText;
    }
}

/**
 * @param {HTMLElement} container
 * @param {'user'|'assistant'|'error'} role
 * @param {string} text
 * @param {string[]} [chips]
 * @param {string} [notice]
 */
function appendMessage(container, role, text, chips = [], notice = '') {
    if (!container) {
        return;
    }
    const wrap = document.createElement('div');
    wrap.className = `dual-ia-msg dual-ia-msg--${role}`;

    if (notice) {
        const n = document.createElement('p');
        n.className = 'dual-ia-fallback-notice';
        n.textContent = notice;
        wrap.appendChild(n);
    }

    const body = document.createElement('div');
    body.className = 'dual-ia-msg-body';
    if (role === 'user') {
        body.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
    } else {
        body.textContent = text;
    }
    wrap.appendChild(body);

    if (chips.length > 0) {
        const chipRow = document.createElement('div');
        chipRow.className = 'dual-ia-chips';
        chipRow.setAttribute('role', 'group');
        chipRow.setAttribute('aria-label', 'Preguntas sugeridas de seguimiento');
        chips.forEach((q) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'dual-ia-chip';
            b.textContent = q;
            b.setAttribute('aria-label', `Preguntar: ${q}`);
            chipRow.appendChild(b);
        });
        wrap.appendChild(chipRow);
    }

    container.appendChild(wrap);
    wrap.scrollIntoView({ behavior: 'smooth', block: 'end' });
    return wrap;
}

/**
 * Enlaza chips para rellenar el campo y reenviar (usa el objetivo de envío actual).
 * @param {HTMLElement | null} wrap
 * @param {HTMLTextAreaElement | null} inputEl
 * @param {HTMLFormElement | null} formEl
 */
function wireChips(wrap, inputEl, formEl) {
    wrap?.querySelectorAll('.dual-ia-chip').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (inputEl) {
                inputEl.value = btn.textContent || '';
                inputEl.focus();
            }
            formEl?.requestSubmit();
        });
    });
}

/**
 * @param {HTMLFormElement} form
 */
function getSendTarget(form) {
    const el = form.querySelector('input[name="dual-send-target"]:checked');
    return el ? el.value : 'both';
}

function initMobileTabs(root) {
    const tabs = root.querySelectorAll('.dual-ia-tab');
    const panels = root.querySelectorAll('.dual-ia-col');
    if (!tabs.length || !panels.length) {
        return;
    }
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab-target');
            tabs.forEach((t) => {
                const sel = t.getAttribute('data-tab-target') === target;
                t.setAttribute('aria-selected', sel ? 'true' : 'false');
            });
            panels.forEach((p) => {
                const match = p.getAttribute('data-col') === target;
                p.classList.toggle('is-mobile-active', match);
            });
        });
    });
}

/**
 * Inicializa el comparador si existe el contenedor en la página.
 */
export function initDualChat() {
    const root = document.getElementById('dual-ia-root');
    if (!root) {
        return;
    }

    const apiBase = root.dataset.apiBase || '/api';
    const form = document.getElementById('dual-ia-form');
    const input = document.getElementById('dual-ia-input');
    const submitBtn = document.getElementById('dual-ia-submit');
    const colGroq = document.getElementById('dual-msgs-groq');
    const colOllama = document.getElementById('dual-msgs-ollama');
    const statusGroq = document.getElementById('dual-status-groq');
    const statusOllama = document.getElementById('dual-status-ollama');

    initMobileTabs(root);

    async function refreshStatus() {
        const data = await fetchStatus(apiBase);
        if (!data) {
            renderConnectionStatus(statusGroq, false, 'En línea', 'Sin conexión');
            renderConnectionStatus(statusOllama, false, 'En línea', 'Sin conexión');
            return;
        }
        renderConnectionStatus(statusGroq, !!data.llama_available, 'En línea', 'Sin conexión');
        renderConnectionStatus(statusOllama, !!data.ollama_available, 'En línea', 'Sin conexión');
    }

    refreshStatus();
    setInterval(refreshStatus, 25000);

    /**
     * @param {'groq'|'ollama'} provider
     * @param {string} question
     */
    async function runColumn(provider, question) {
        const col = provider === 'groq' ? colGroq : colOllama;
        const { ok, json } = await postAsk(apiBase, question, provider);
        if (ok && json.success) {
            const ans = json.data?.answer ?? '';
            const chips = json.data?.suggested_questions ?? [];
            const notice = json.data?.fallback_used && json.data?.fallback_notice ? json.data.fallback_notice : '';
            const wrap = appendMessage(col, 'assistant', ans, chips, notice);
            wireChips(wrap, input, form);
        } else {
            const err = json.error || json.message || 'Error al consultar el modelo.';
            appendMessage(col, 'error', err, []);
        }
    }

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const q = (input?.value || '').trim();
        if (!q || !submitBtn) {
            return;
        }

        const target = getSendTarget(form);
        submitBtn.disabled = true;

        if (target === 'both' || target === 'groq') {
            appendMessage(colGroq, 'user', q, []);
        }
        if (target === 'both' || target === 'ollama') {
            appendMessage(colOllama, 'user', q, []);
        }

        const tasks = [];
        if (target === 'both' || target === 'groq') {
            tasks.push(runColumn('groq', q));
        }
        if (target === 'both' || target === 'ollama') {
            tasks.push(runColumn('ollama', q));
        }

        try {
            await Promise.all(tasks);
        } finally {
            submitBtn.disabled = false;
            if (input) {
                input.value = '';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDualChat();
});
