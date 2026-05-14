@extends('layouts.app')

@section('title', 'Emergencia · ¿Y qué hago?')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <a class="ov-back" href="{{ route('home') }}" aria-label="Regresar"><span aria-hidden="true">‹</span></a>

            <div id="ov-urg-step1" class="ov-step-panel">
                <span class="ov-flow-eyebrow">Emergencia</span>
                <span class="ov-step-pill" id="ov-urg-pill-1">Paso 1 de 3</span>
                <h1 class="ov-h2" style="margin-top: 0; color: var(--color-secondary);">¿Qué describe mejor tu situación?</h1>
                <p class="ov-p">Selecciona la opción que mejor describa tu situación para poder orientarte de inmediato.</p>

                <div class="ov-choice-list" role="list">
                    <button type="button" class="ov-choice-card" data-situation="notificacion_urgente" role="listitem">
                        <span class="ov-choice-card-title">Me notificaron algo urgente</span>
                        <span class="ov-choice-card-sub">Recibí un aviso con poco tiempo para responder</span>
                    </button>
                    <button type="button" class="ov-choice-card" data-situation="documento_plazo" role="listitem">
                        <span class="ov-choice-card-title">Recibí un documento oficial con plazo corto</span>
                        <span class="ov-choice-card-sub">Tengo una fecha límite para actuar</span>
                    </button>
                    <button type="button" class="ov-choice-card" data-situation="violencia" role="listitem">
                        <span class="ov-choice-card-title">Me violentaron</span>
                        <span class="ov-choice-card-sub">Necesito orientación sobre cómo responder</span>
                    </button>
                    <button type="button" class="ov-choice-card" data-situation="no_entiendo" role="listitem">
                        <span class="ov-choice-card-title">No entiendo qué hacer</span>
                        <span class="ov-choice-card-sub">Me llegó un documento y no sé por dónde empezar</span>
                    </button>
                    <button type="button" class="ov-choice-card" data-situation="documento_sin_plan" role="listitem">
                        <span class="ov-choice-card-title">Tengo un documento y no sé qué hacer</span>
                        <span class="ov-choice-card-sub">Sé que es oficial, pero no entiendo los siguientes pasos</span>
                    </button>
                </div>
            </div>

            <div id="ov-urg-step2" class="ov-step-panel" hidden>
                <span class="ov-flow-eyebrow">Emergencia</span>
                <span class="ov-step-pill" id="ov-urg-pill-2">Paso 2 de 3</span>
                <h1 class="ov-h2" style="margin-top: 0; color: var(--color-secondary);">¿Tienes el documento contigo?</h1>
                <p class="ov-p">Esto nos ayuda a entender tu caso más rápido y con mayor precisión.</p>

                <div class="ov-card-shell">
                    <button type="button" class="action-card" id="ov-urg-scan" style="width: 100%; border: none; cursor: pointer; text-align: left;">
                        <div class="action-card-left">
                            <span class="action-card-icon" aria-hidden="true">📷</span>
                            <div>
                                <div class="action-card-title">Escanear con cámara</div>
                                <div class="action-card-subtitle">Toma una foto al documento</div>
                            </div>
                        </div>
                    </button>
                    <label class="action-card" style="margin-top: 12px; cursor: pointer; display: flex;">
                        <div class="action-card-left">
                            <span class="action-card-icon" aria-hidden="true">📄</span>
                            <div>
                                <div class="action-card-title">Subir archivo</div>
                                <div class="action-card-subtitle">Selecciona un archivo PDF</div>
                            </div>
                        </div>
                        <input type="file" id="ov-urg-file" accept=".pdf,application/pdf" class="sr-only">
                    </label>
                    <button type="button" class="action-card" id="ov-urg-describe" style="width: 100%; margin-top: 12px; border: none; cursor: pointer; text-align: left;">
                        <div class="action-card-left">
                            <span class="action-card-icon" aria-hidden="true">✍️</span>
                            <div>
                                <div class="action-card-title">No, describir la situación</div>
                                <div class="action-card-subtitle">Escribe lo que entiendas del documento</div>
                            </div>
                        </div>
                    </button>
                    <button type="button" class="ov-foot-link" id="ov-urg-doc-help">¿Qué documentos puedo subir?</button>
                </div>
            </div>

            <div id="ov-urg-step3" class="ov-step-panel" hidden>
                <span class="ov-flow-eyebrow" id="ov-urg-step3-eyebrow">Emergencia</span>
                <span class="ov-step-pill" id="ov-urg-pill-3">Paso 3 de 3</span>
                <div class="ov-card-shell">
                    <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 900;">Describe tu situación</h2>
                    <p class="ov-p" style="margin-bottom: 12px;">No importa si no sabes los términos legales. Escribe lo que recuerdes del documento.</p>
                    <label class="sr-only" for="ov-emergency-text">Escribe tu situación y lo que recuerdes del documento</label>
                    <textarea id="ov-emergency-text" class="ov-textarea-full" rows="5" placeholder="Escribe tu situación y lo que recuerdes del documento"></textarea>
                    <button id="ov-emergency-send" type="button" class="ov-btn ov-btn-primary" style="margin-top: 12px;">Enviar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const p1 = document.getElementById('ov-urg-step1');
        const p2 = document.getElementById('ov-urg-step2');
        const p3 = document.getElementById('ov-urg-step3');
        const ta = document.getElementById('ov-emergency-text');
        const send = document.getElementById('ov-emergency-send');
        const file = document.getElementById('ov-urg-file');
        const describeBtn = document.getElementById('ov-urg-describe');
        const scanBtn = document.getElementById('ov-urg-scan');
        const docHelp = document.getElementById('ov-urg-doc-help');

        function showStep(n) {
            p1.hidden = n !== 1;
            p2.hidden = n !== 2;
            p3.hidden = n !== 3;
        }

        document.querySelectorAll('.ov-choice-card').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ov-choice-card').forEach((b) => b.classList.remove('is-selected'));
                btn.classList.add('is-selected');
                const situation = btn.getAttribute('data-situation') || '';
                try {
                    sessionStorage.setItem('ov_situation', situation);
                } catch (e) {}
                showStep(2);
            });
        });

        describeBtn?.addEventListener('click', () => showStep(3));

        scanBtn?.addEventListener('click', () => {
            alert('La cámara en el navegador se integrará en una siguiente versión. Por ahora puedes subir un PDF o describir tu situación.');
        });

        file?.addEventListener('change', () => {
            if (file.files?.length) {
                try {
                    sessionStorage.setItem('ov_last_emergency', 'Archivo PDF seleccionado (demo).');
                } catch (e) {}
                window.location.href = @json(route('procesando'));
            }
        });

        send?.addEventListener('click', () => {
            const v = (ta?.value || '').trim();
            if (!v) {
                ta?.focus();
                return;
            }
            try {
                sessionStorage.setItem('ov_last_emergency', v);
            } catch (e) {}
            window.location.href = @json(route('procesando'));
        });

        docHelp?.addEventListener('click', () => {
            alert('Puedes subir un PDF legible. No guardamos el archivo en servidores en esta demo local.');
        });

        const params = new URLSearchParams(window.location.search);
        if (params.get('step') === '3') {
            document.getElementById('ov-urg-step3-eyebrow').textContent = 'Situación';
            showStep(3);
        }
    })();
</script>
@endpush
