@extends('layouts.app')

@section('title', 'Procesando · OrientaVox')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
        .ov-progress {
            margin-top: 10px;
            height: 10px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--color-text) 10%, transparent);
            overflow: hidden;
        }
        .ov-progress > div {
            height: 100%;
            width: 18%;
            background: var(--color-primary);
            border-radius: 999px;
            transition: width 0.35s ease-out;
        }
        .ov-status-list { margin-top: 18px; display: grid; gap: 12px; }
        .ov-status {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid var(--color-border-subtle);
            background: color-mix(in srgb, var(--color-surface) 92%, transparent);
        }
        .ov-status-dot {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 900;
            border: 1px solid var(--color-border-subtle);
            background: transparent;
            color: var(--color-text-muted);
        }
        .ov-status[data-done="true"] .ov-status-dot {
            background: color-mix(in srgb, var(--color-primary) 18%, var(--color-surface) 82%);
            color: var(--color-primary);
            border-color: var(--color-primary);
        }
        .ov-muted { color: var(--color-text-muted); font-size: 13px; }
        .ov-lock { margin-top: 14px; text-align: center; font-size: 12px; color: var(--color-text-muted); }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell ov-center">
            <div class="ov-hero-icon" aria-hidden="true">
                <div class="ov-lupin"></div>
            </div>

            <h1 class="ov-h2">Respira. Estoy contigo</h1>
            <p class="ov-p">Lo resolveremos paso a paso</p>
            <p class="ov-muted">Esto puede tomar unos segundos…</p>

            <div class="ov-progress" aria-label="Progreso">
                <div id="ov-progress-bar"></div>
            </div>

            <div class="ov-status-list" role="list" aria-label="Pasos">
                <div class="ov-status" role="listitem" id="st-1" data-done="true">
                    <span class="ov-status-dot" aria-hidden="true">✓</span>
                    <span>Revisando documento</span>
                </div>
                <div class="ov-status" role="listitem" id="st-2" data-done="true">
                    <span class="ov-status-dot" aria-hidden="true">✓</span>
                    <span>Identificando tipo de procedimiento</span>
                </div>
                <div class="ov-status" role="listitem" id="st-3" data-done="true">
                    <span class="ov-status-dot" aria-hidden="true">✓</span>
                    <span>Consultando fuentes oficiales</span>
                </div>
                <div class="ov-status" role="listitem" id="st-4" data-done="false">
                    <span class="ov-status-dot" aria-hidden="true">⋯</span>
                    <span>Generando orientación</span>
                </div>
            </div>

            <div class="ov-lock">🔒 No guardamos ningún dato sensible</div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const bar = document.getElementById('ov-progress-bar');
        const st4 = document.getElementById('st-4');
        let p = 18;
        const t = setInterval(() => {
            p = Math.min(100, p + 9);
            if (bar) bar.style.width = p + '%';
        }, 350);
        setTimeout(() => {
            if (st4) {
                st4.dataset.done = 'true';
                const dot = st4.querySelector('.ov-status-dot');
                if (dot) dot.textContent = '✓';
            }
            clearInterval(t);
            setTimeout(() => { window.location.href = @json(route('salida')); }, 650);
        }, 2200);
    })();
</script>
@endpush

