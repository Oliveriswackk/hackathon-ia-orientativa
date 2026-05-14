@extends('layouts.app')

@section('title', 'Bienvenida · ¿Y qué hago?')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <a class="ov-back" href="{{ route('home') }}" aria-label="Regresar">
                <span aria-hidden="true">‹</span>
            </a>

            <div class="ov-hero-icon">
                <img class="ov-lupin-img" src="{{ asset('images/lupin-mascot.png') }}" width="88" height="88" alt="Lupin, tu orientador">
            </div>

            <h1 class="ov-h1">OrientaVox</h1>
            <p class="ov-p" style="margin-top: -6px; font-size: 14px; font-weight: 600; color: var(--color-text);">¿Y qué hago? · Herramienta ciudadana</p>
            <p class="ov-p">Entiende. Actúa. Protege tus derechos</p>

            <div class="ov-checklist" role="list" aria-label="Beneficios">
                <div class="ov-check" role="listitem"><span class="ov-check-dot" aria-hidden="true">✓</span> Analizamos</div>
                <div class="ov-check" role="listitem"><span class="ov-check-dot" aria-hidden="true">✓</span> Explicamos</div>
                <div class="ov-check" role="listitem"><span class="ov-check-dot" aria-hidden="true">✓</span> Te guiamos</div>
                <div class="ov-check" role="listitem"><span class="ov-check-dot" aria-hidden="true">✓</span> Protegemos tus derechos</div>
            </div>

            <a class="ov-btn ov-btn-primary" href="{{ route('onboarding.2') }}">Continuar <span aria-hidden="true">›</span></a>

            <div class="ov-dots" aria-label="Progreso">
                <span class="ov-dot ov-dot--active" aria-hidden="true"></span>
                <span class="ov-dot" aria-hidden="true"></span>
                <span class="ov-dot" aria-hidden="true"></span>
            </div>
        </div>
    </div>
@endsection

