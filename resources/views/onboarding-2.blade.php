@extends('layouts.app')

@section('title', 'Antes de empezar · ¿Y qué hago?')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <a class="ov-back" href="{{ route('onboarding.1') }}" aria-label="Regresar">
                <span aria-hidden="true">‹</span>
            </a>

            <div class="ov-shield" aria-hidden="true">🛡️</div>
            <h1 class="ov-h2">Así de fácil funciona</h1>

            <ol class="ov-steps">
                <li><span class="ov-step-num">1</span> Envíanos tu documento o cuéntanos tu situación</li>
                <li><span class="ov-step-num">2</span> Nuestra IA legal lo analiza al instante</li>
                <li><span class="ov-step-num">3</span> Recibe claridad, con quién ir y qué pasa si no actúas</li>
                <li><span class="ov-step-num">4</span> Tú decides y actúas a tiempo</li>
            </ol>

            <div class="ov-disclaimer">
                OrientaVox ofrece orientación inicial y <strong>no sustituye asesoría profesional</strong>.
            </div>

            <div class="ov-consent">
                <label class="ov-checkbox">
                    <input type="checkbox" id="ov-terms-privacy">
                    <span>He leído y acepto los <u>términos</u> y el <u>aviso de privacidad</u></span>
                </label>
            </div>

            <button class="ov-btn ov-btn-primary" id="ov-consent-continue" disabled>
                Continuar <span aria-hidden="true">›</span>
            </button>

            <div class="ov-dots" aria-label="Progreso">
                <span class="ov-dot" aria-hidden="true"></span>
                <span class="ov-dot ov-dot--active" aria-hidden="true"></span>
                <span class="ov-dot" aria-hidden="true"></span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const c = document.getElementById('ov-terms-privacy');
        const btn = document.getElementById('ov-consent-continue');
        function sync() {
            btn.disabled = !c?.checked;
        }
        c?.addEventListener('change', sync);
        btn?.addEventListener('click', () => {
            window.location.href = @json(route('onboarding.3'));
        });
    })();
</script>
@endpush
