@extends('layouts.app')

@section('title', 'Traductor de documentos · ¿Y qué hago?')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .translator-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            gap: 28px;
            padding-top: 40px;
            padding-bottom: 40px;
        }

        @media (max-width: 900px) {
            .translator-layout {
                grid-template-columns: 1fr;
            }
        }

        .translator-col {
            min-width: 0;
        }

        .translator-title {
            font-size: clamp(20px, 3vw, 32px);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .translator-subtitle {
            font-size: 15px;
            color: var(--color-text-muted);
            margin-bottom: 18px;
            max-width: 65ch;
            line-height: 1.6;
        }

        .type-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .type-pill {
            border-radius: 999px;
            padding: 7px 12px;
            border: 1px solid var(--color-border-subtle);
            background: var(--color-surface-elevated);
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: var(--color-text);
            transition: opacity 0.18s ease-out, transform 0.18s ease-out;
        }

        .type-pill[data-active="true"] {
            border-color: var(--color-primary);
            background: color-mix(in srgb, var(--color-primary) 18%, var(--color-surface) 82%);
            color: var(--color-text);
        }

        .textarea-doc {
            width: 100%;
            border-radius: 16px;
            border: 1px solid var(--color-border-strong);
            background: var(--color-surface);
            color: var(--color-text);
            padding: 12px 14px;
            min-height: 220px;
            font-size: 15px;
            resize: vertical;
            line-height: 1.6;
        }

        .textarea-doc::placeholder {
            color: var(--color-text-muted);
        }

        .btn-loading {
            opacity: 0.85;
            cursor: default;
        }

        .btn-spinner {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            border: 2px solid var(--color-border-subtle);
            border-top-color: var(--color-primary);
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .translator-output-placeholder {
            padding: 18px 16px;
            text-align: center;
            font-size: 14px;
            color: var(--color-text-muted);
            line-height: 1.6;
            max-width: 65ch;
            margin-left: auto;
            margin-right: auto;
        }

        .translator-output-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        @media (max-width: 900px) {
            .translator-output-grid {
                grid-template-columns: 1fr;
            }
        }

        .alert-urgency {
            background: color-mix(in srgb, var(--color-accent) 32%, var(--color-surface) 68%);
            color: var(--color-text);
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 10px;
            border: 1px solid var(--color-border-strong);
            line-height: 1.6;
            max-width: 65ch;
        }

        .translator-label-row {
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--color-text-muted);
        }

        .translator-hint {
            font-size: 12px;
            color: var(--color-text-muted);
            line-height: 1.6;
            max-width: 65ch;
        }

        .translator-plazo-destacado {
            font-size: 16px;
            font-weight: 700;
            color: var(--color-primary);
            margin-top: 4px;
        }

        #urgencyChip {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
            display: none;
            align-items: center;
        }

        #urgencyChip[data-level="alto"] {
            border-color: var(--color-border-strong);
            background: color-mix(in srgb, var(--color-secondary) 25%, var(--color-surface) 75%);
            color: var(--color-text);
        }

        #urgencyChip[data-level="medio"] {
            border-color: var(--color-primary);
            background: color-mix(in srgb, var(--color-primary) 15%, var(--color-surface) 85%);
            color: var(--color-text);
        }

        #urgencyChip[data-level="bajo"] {
            border-color: var(--color-border-subtle);
            background: transparent;
            color: var(--color-text-muted);
        }

        .translator-link-oficial {
            color: var(--color-primary);
            text-decoration: underline;
            word-break: break-all;
        }
    </style>
@endpush

@section('content')
    <div class="page-container translator-ov-page">
        <section class="translator-layout fade-up">
            <div class="translator-col">
                <span class="ov-flow-eyebrow" style="margin-top: 0;">Traductor</span>
                <h1 class="translator-title">Analiza tu documento</h1>
                <p class="translator-subtitle">
                    Pega aquí tu acuerdo, resolución o notificación electoral. Te mostraremos los plazos aproximados, consecuencias
                    de no actuar y una ruta institucional sugerida. Esta información es orientativa.
                </p>

                <div class="glass-card" style="padding: 16px 16px 18px; margin-top: 10px;">
                    <div class="translator-label-row">
                        Tipo de documento
                    </div>
                    <div class="type-selector" id="tipoSelector">
                        <button type="button" class="type-pill" data-value="acuerdo_requerimiento">
                            <span>🗂</span> Acuerdo de requerimiento
                        </button>
                        <button type="button" class="type-pill" data-value="notificacion_resolucion">
                            <span>📬</span> Notificación de resolución
                        </button>
                        <button type="button" class="type-pill" data-value="resolucion_medio">
                            <span>⚖️</span> Resolución de medio de impugnación
                        </button>
                        <button type="button" class="type-pill" data-value="acuerdo_desechamiento">
                            <span>🚫</span> Acuerdo de desechamiento o improcedencia
                        </button>
                    </div>

                    <div style="margin-top: 10px;">
                        <textarea id="docTexto" class="textarea-doc" placeholder="Pega el contenido completo de tu documento o, al menos, el párrafo donde se hable de plazos, requerimientos o resoluciones."></textarea>
                    </div>

                    <div style="margin-top: 12px; display:flex; flex-direction:column; gap:6px;">
                        <button class="btn btn-primary" type="button" id="btnAnalizarDoc">
                            <span id="btnAnalizarLabel">Entender mi documento</span>
                            <span id="btnSpinner" class="btn-spinner" style="display:none;"></span>
                        </button>
                        <p class="translator-hint">
                            Demo sugerido: pega un <strong>acuerdo de requerimiento</strong> que mencione expresamente
                            un <strong>plazo de 3 días hábiles</strong>. Verás cómo se actualiza el nivel de urgencia.
                        </p>
                    </div>
                </div>
            </div>

            <div class="translator-col fade-in" id="outputCol">
                <div class="glass-card" style="padding: 16px 16px 18px;">
                    <div class="translator-label-row" style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
                        <span>Resultado estructurado</span>
                        <span id="urgencyChip" data-level="bajo"></span>
                    </div>
                    <div id="translatorOutput">
                        <div class="translator-output-placeholder">
                            <div style="font-size: 22px; margin-bottom: 6px;">🧩</div>
                            El resumen de tu documento aparecerá aquí. Aún no hemos detectado ningún texto para analizar.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const tipoSelector = document.getElementById('tipoSelector');
        const docTexto = document.getElementById('docTexto');
        const btnAnalizar = document.getElementById('btnAnalizarDoc');
        const btnLabel = document.getElementById('btnAnalizarLabel');
        const btnSpinner = document.getElementById('btnSpinner');
        const output = document.getElementById('translatorOutput');
        const urgencyChip = document.getElementById('urgencyChip');

        let tipoSeleccionado = '';

        function setTipo(value) {
            tipoSeleccionado = value;
            document.querySelectorAll('.type-pill').forEach(p => {
                p.setAttribute('data-active', p.dataset.value === value ? 'true' : 'false');
            });
        }

        if (tipoSelector) {
            tipoSelector.addEventListener('click', (e) => {
                const pill = e.target.closest('.type-pill');
                if (!pill) return;
                setTipo(pill.dataset.value);
            });
        }

        function construirTarjeta(label, valor, extraClass = '') {
            return `
                <div class="mini-card ${extraClass}">
                    <div class="mini-card-label">${label}</div>
                    <div class="mini-card-value">${valor}</div>
                </div>
            `;
        }

        async function analizar() {
            const texto = docTexto.value || '';
            if (!tipoSeleccionado && !texto.trim()) {
                output.innerHTML = '<div class="translator-output-placeholder">Selecciona un tipo de documento o pega un texto para poder mostrar un resumen orientativo.</div>';
                return;
            }

            btnAnalizar.classList.add('btn-loading');
            btnAnalizar.disabled = true;
            btnSpinner.style.display = 'inline-block';
            btnLabel.textContent = 'Analizando...';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('/traductor/analizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        texto: texto,
                        tipo: tipoSeleccionado,
                        _token: token
                    })
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Error al analizar el documento');
                }

                // Construir las tarjetas con los datos de la IA
                const plazoTexto = data.plazo_dias > 0 
                    ? `${data.plazo_dias} ${data.tipo_dias}`
                    : 'No identificado claramente';
                
                const plazoDiasTexto = data.plazo_dias > 0
                    ? `<div class="translator-plazo-destacado">Hoy es día 1 de ${data.plazo_dias} días ${data.tipo_dias}</div>`
                    : '';

                const grid = `
                    ${plazoDiasTexto}
                    <div class="translator-output-grid" style="margin-top: ${data.plazo_dias > 0 ? '10px' : '0'};">
                        ${construirTarjeta('🗂 Tipo de documento', data.tipo_documento, 'mini-card--highlight')}
                        ${construirTarjeta('⏱ Plazo procesal', plazoTexto, 'mini-card--highlight')}
                        ${construirTarjeta('📅 Inicio del plazo', data.inicio_computo, '')}
                        ${construirTarjeta('⚠️ Consecuencia de no actuar', data.consecuencia, '')}
                        ${construirTarjeta('🏛 Autoridad emisora', data.autoridad, '')}
                        ${construirTarjeta('✅ Ruta sugerida', data.accion_inmediata, 'mini-card--highlight')}
                        ${construirTarjeta('📄 Traducción simple', data.traduccion_simple, '')}
                        ${construirTarjeta('🔗 Link oficial', `<a href="${data.link_oficial}" target="_blank" rel="noopener noreferrer" class="translator-link-oficial">${data.link_oficial}</a>`, '')}
                    </div>
                    <p class="translator-hint" style="margin-top: 10px;">
                        Esta información es orientativa y no sustituye la asesoría de la autoridad electoral ni de un profesional en derecho.
                    </p>
                `;

                let urgenciaTexto = '';
                const urgencia = data.nivel_urgencia || 'MEDIO';
                urgencyChip.style.display = 'inline-flex';
                if (urgencia === 'ALTO') {
                    urgencyChip.dataset.level = 'alto';
                    urgenciaTexto = 'NIVEL: ALTO';
                } else if (urgencia === 'MEDIO') {
                    urgencyChip.dataset.level = 'medio';
                    urgenciaTexto = 'NIVEL: MEDIO';
                } else {
                    urgencyChip.dataset.level = 'bajo';
                    urgenciaTexto = 'NIVEL: BAJO';
                }
                urgencyChip.textContent = urgenciaTexto;

                output.innerHTML = '';
                if (urgencia === 'ALTO') {
                    const alert = document.createElement('div');
                    alert.className = 'alert-urgency';
                    alert.innerHTML = '<strong>Urgencia alta:</strong> el tiempo puede estar corriendo. Considera actuar hoy mismo y confirma con la autoridad si aún estás en plazo.';
                    output.appendChild(alert);
                }
                const wrapper = document.createElement('div');
                wrapper.innerHTML = grid;
                output.appendChild(wrapper);

            } catch (error) {
                output.innerHTML = `<div class="translator-output-placeholder" style="color: var(--color-secondary);">Error de conexión: ${error.message}. Intenta de nuevo.</div>`;
            } finally {
                btnAnalizar.classList.remove('btn-loading');
                btnAnalizar.disabled = false;
                btnSpinner.style.display = 'none';
                btnLabel.textContent = 'Entender mi documento';
                document.getElementById('outputCol').classList.add('in-view');
            }
        }

        if (btnAnalizar) {
            btnAnalizar.addEventListener('click', analizar);
        }
    })();
</script>
@endpush



