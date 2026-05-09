@extends('layouts.app')

@section('title', 'Modo urgencia · ¿Y qué hago?')

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

            <h1 class="ov-h2" style="margin-top: 52px; color: var(--color-secondary);">Emergencia</h1>
            <p class="ov-p">Paso 3 de 3</p>

            <div class="ov-card" style="margin-top: 10px;">
                <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 900;">Describe tu situación</h2>
                <p class="ov-p" style="margin-bottom: 12px;">No importa si no sabes los términos legales. Escribe lo que recuerdes del documento.</p>

                <textarea id="ov-emergency-text" style="width: 100%; min-height: 140px; border-radius: 18px; border: 1px solid var(--color-border-strong); padding: 14px; font: inherit; background: var(--color-surface);"></textarea>
                <button id="ov-emergency-send" class="ov-btn ov-btn-primary" style="margin-top: 12px;">Enviar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const t = document.getElementById('ov-emergency-text');
        const btn = document.getElementById('ov-emergency-send');
        btn?.addEventListener('click', () => {
            const v = (t?.value || '').trim();
            localStorage.setItem('ov_last_emergency', v);
            window.location.href = @json(route('procesando'));
        });
    })();
</script>
@endpush
