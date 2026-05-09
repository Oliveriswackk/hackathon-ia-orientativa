@extends('layouts.app')

@section('title', 'Salida · OrientaVox')

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
        }
        .ov-bottom .btn { flex: 1; padding: 12px 14px; border-radius: 16px; }
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
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <div class="ov-alert">
                <span aria-hidden="true">⏱️</span>
                <span><strong>Plazo corto para responder</strong></span>
            </div>

            <div class="ov-card">
                <h1 style="margin: 0 0 12px; font-size: 18px; font-weight: 900;">Tu plazo para actuar</h1>

                <div class="ov-deadline">
                    <div class="ov-deadline-box">
                        <div class="ov-label">Días</div>
                        <div class="ov-big">3<small>hábiles</small></div>
                    </div>
                    <div class="ov-deadline-box">
                        <div class="ov-label">Fecha límite</div>
                        <div style="margin-top: 8px; font-weight: 800;">17 abr 2026</div>
                        <div style="margin-top: 2px; color: var(--color-text-muted); font-size: 13px;">(ejemplo demo)</div>
                    </div>
                </div>

                <div class="ov-divider"></div>

                <div class="ov-kv">
                    <h2>Qué recibiste</h2>
                    <p><strong>Cédula de Notificación</strong><br><span class="ov-muted">Expediente: JDC-013/2026</span></p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>Qué significa</h2>
                    <p>Se inició un procedimiento en tu contra. Puedes responder o presentar pruebas.</p>
                    <div class="ov-divider"></div>
                </div>

                <div class="ov-kv">
                    <h2>Qué debes hacer ahora</h2>
                    <ol class="ov-list">
                        <li>Revisar contenido del documento</li>
                        <li>Preparar respuesta o pruebas</li>
                        <li>Presentar respuesta antes del plazo</li>
                    </ol>
                </div>
            </div>

            <div class="ov-bot">
                <div class="ov-bot-avatar" aria-hidden="true"></div>
                <div>
                    <div style="font-weight: 900; margin-bottom: 3px;">Lupin - Tu orientador</div>
                    <p><strong>Estoy para ayudarte.</strong> Lo más importante es no dejar pasar el plazo. ¿Aclaré tu escenario?</p>
                </div>
            </div>

            <div class="ov-bottom">
                <a class="btn btn-primary" href="{{ route('home') }}">Sí, entendido</a>
                <a class="btn btn-secondary" href="{{ route('home') }}">No tengo dudas</a>
            </div>
        </div>
    </div>
@endsection

