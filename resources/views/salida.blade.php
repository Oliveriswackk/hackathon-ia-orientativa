@extends('layouts.app')

@section('title', 'Resultado · ¿Y qué hago?')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }

        .ov-card {
            border-radius: 22px;
            border: 1px solid var(--color-border-subtle);
            background: color-mix(in srgb, var(--color-surface) 94%, transparent);
            box-shadow: 0 12px 30px var(--color-shadow-soft);
            padding: 16px;
        }
        .ov-alert {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--color-secondary) 55%, var(--color-border-subtle) 45%);
            background: color-mix(in srgb, var(--color-secondary) 10%, var(--color-surface) 90%);
            color: var(--color-text);
            font-size: 13px;
            margin-bottom: 12px;
        }
        .ov-deadline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            align-items: stretch;
        }
        @media (max-width: 420px) {
            .ov-deadline { grid-template-columns: 1fr; }
        }
        .ov-deadline-box {
            border-radius: 18px;
            border: 1px solid var(--color-border-subtle);
            background: var(--color-surface);
            padding: 12px;
        }
        .ov-label { font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .08em; }
        .ov-big { font-size: 40px; font-weight: 900; letter-spacing: -0.03em; color: var(--color-secondary); line-height: 1; }
        .ov-big small { font-size: 16px; color: var(--color-text); font-weight: 700; margin-left: 6px; }
        .ov-kv { margin-top: 14px; }
        .ov-kv h2 { margin: 0 0 10px; font-size: 15px; font-weight: 800; }
        .ov-kv p { margin: 0; color: var(--color-text); }
        .ov-divider { height: 1px; background: var(--color-border-subtle); margin: 14px 0; }
        .ov-list { margin: 0; padding-left: 18px; color: var(--color-text); }
        .ov-bottom {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ov-bottom .ov-chip { flex: 1 1 calc(50% - 5px); text-align: center; }
        .ov-bot {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 64px 1fr;
            gap: 12px;
            align-items: center;
        }
        .ov-bot-avatar {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: radial-gradient(circle at 35% 35%, color-mix(in srgb, var(--color-primary) 20%, transparent), transparent 70%);
            display: grid;
            place-items: center;
        }
        .ov-bot-avatar::before {
            content: '🔎';
            font-size: 28px;
        }
        .ov-bot p { margin: 0; color: var(--color-text-muted); }
        .ov-bot strong { color: var(--color-text); }
        .ov-datos-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 800;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .ov-datos-bar a {
            font-size: 13px;
            font-weight: 700;
            color: var(--color-primary);
            text-decoration: none;
        }
        .ov-datos-bar a:hover { text-decoration: underline; }
        .ov-link-list { margin: 0; padding: 0; list-style: none; display: grid; gap: 8px; }
        .ov-link-list a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: underline;
            word-break: break-word;
        }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <a class="ov-back" href="{{ route('home') }}" aria-label="Regresar"><span aria-hidden="true">‹</span></a>

            <div class="ov-alert">
                <span aria-hidden="true">⏱️</span>
                <span><strong>Actúa antes del plazo</strong> — Tienes poco tiempo para responder</span>
            </div>

            <div class="ov-datos-bar">
                <span>Datos analizados</span>
                <a href="#ov-resultado-detalle">Revisar</a>
            </div>

            <div class="ov-card" id="ov-resultado-detalle">
                <h1 style="margin: 0 0 12px; font-size: 18px; font-weight: 900;">Tu plazo para actuar</h1>

                <div class="ov-deadline">
                    <div class="ov-deadline-box">
                        <div class="ov-label">Días</div>
                        <div class="ov-big">3<small>hábiles</small></div>
                    </div>
                    <div class="ov-deadline-box">
                        <div class="ov-label">Fecha límite</div>
                        <div style="margin-top: 8px; font-weight: 800;">17 abr 2026</div>
                        <div class="ov-muted" style="margin-top: 2px;">(ejemplo demo)</div>
                    </div>
                </div>

                <div class="ov-divider"></div>

                <div class="ov-kv">
                    <h2>Qué recibiste</h2>
                    <p><strong>Cédula de notificación</strong><br><span class="ov-muted">Expediente: JDC-013/2026</span></p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>Institución</h2>
                    <p><strong>TEPJF</strong> <span class="ov-muted">(Tribunal Electoral del Poder Judicial de la Federación)</span></p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>¿Qué significa?</h2>
                    <p>Se inició un procedimiento en tu contra. Puedes responder o presentar pruebas dentro del plazo indicado en el documento.</p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>Riesgo de no actuar</h2>
                    <p>Si no respondes a tiempo, podrías perder oportunidades de defensa o que se resuelva en tu ausencia, según el tipo de procedimiento.</p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>¿Qué puedo hacer ahora?</h2>
                    <ol class="ov-list">
                        <li>Revisar el contenido del documento y las fechas clave.</li>
                        <li>Preparar respuesta o pruebas con apoyo profesional si aplica.</li>
                        <li>Presentar tu respuesta antes del plazo ante la autoridad competente.</li>
                    </ol>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>Links</h2>
                    <ul class="ov-link-list">
                        <li><a href="https://www.tepjf.gob.mx/" target="_blank" rel="noopener noreferrer">Portal del TEPJF</a></li>
                        <li><a href="{{ route('traductor') }}">Volver a analizar otro documento</a></li>
                    </ul>
                </div>
            </div>

            <div class="ov-bot">
                <div class="ov-bot-avatar" aria-hidden="true"></div>
                <div>
                    <div style="font-weight: 900; margin-bottom: 3px;">Lupin — Tu orientador</div>
                    <p><strong>Estoy para ayudarte.</strong> Lo más importante es no dejar pasar el plazo. ¿Te quedó claro el siguiente paso?</p>
                </div>
            </div>

            <div class="ov-bottom">
                <a class="ov-chip ov-chip--primary" href="{{ route('home') }}">Sí, entendido</a>
                <a class="ov-chip" href="{{ route('urgencia') }}?step=3">No, tengo dudas</a>
            </div>

            <div class="ov-chip-row">
                <button type="button" class="ov-chip" id="ov-salida-escuchar" aria-label="Escuchar resumen (demo)">Escuchar</button>
                <a class="ov-chip" href="{{ route('traductor') }}">Subir documento</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('ov-salida-escuchar')?.addEventListener('click', () => {
        if ('speechSynthesis' in window) {
            const t = document.getElementById('ov-resultado-detalle');
            const text = t ? t.innerText.slice(0, 800) : '';
            window.speechSynthesis.cancel();
            const u = new SpeechSynthesisUtterance(text || 'Resumen de demostración.');
            u.lang = 'es-MX';
            window.speechSynthesis.speak(u);
        } else {
            alert('Tu navegador no expone lectura en voz alta aquí. Puedes usar las opciones de accesibilidad del sistema.');
        }
    });
</script>
@endpush
