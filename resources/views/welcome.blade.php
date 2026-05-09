@extends('layouts.app')

@section('title', '¿Y qué hago? · Orientación electoral para jóvenes')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell">
            <header class="mobile-header" style="margin-bottom: 18px;">
                <div class="mobile-brand">Orienta<b>Vox</b></div>
                <div class="mobile-header-actions">
                    <div class="mobile-pill" role="button" aria-label="Idioma">
                        <span aria-hidden="true">🌐</span>
                        <span>Español</span>
                    </div>
                    <a href="{{ route('onboarding.1') }}" class="mobile-icon-btn" aria-label="Onboarding">
                        <span aria-hidden="true">☰</span>
                    </a>
                </div>
            </header>

            <section class="mobile-hero">
                <h1>¿Te llegó un <span style="color: var(--color-primary);">documento oficial</span>?</h1>
                <p>Te explico qué significa, si debes actuar y cuánto tiempo tienes.</p>
            </section>

            <a href="{{ route('orientacion') }}" class="action-card action-card-primary" aria-label="Tengo una situación">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">💬</span>
                    <div>
                        <div class="action-card-title">Tengo una situación</div>
                        <div class="action-card-subtitle">Cuéntame qué pasó y te guío</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('urgencia') }}" class="action-card action-card-danger" aria-label="Tengo una emergencia">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">⚡</span>
                    <div>
                        <div class="action-card-title" style="color: var(--color-secondary);">Tengo una emergencia</div>
                        <div class="action-card-subtitle">Necesito saber qué hacer ya</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('traductor') }}" class="action-card" aria-label="Tengo un documento">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">📄</span>
                    <div>
                        <div class="action-card-title">Tengo un documento</div>
                        <div class="action-card-subtitle">Pégalo y te lo explico</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('salida') }}" class="action-card" aria-label="Vista demo">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">🧪</span>
                    <div>
                        <div class="action-card-title">Vista demo</div>
                        <div class="action-card-subtitle">Documento procesado listo para ver salida</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <div class="mobile-footer-bar" aria-label="Barra informativa">
                <div class="mobile-footer-item">
                    <span aria-hidden="true">🔒</span>
                    <span>Sin dar datos personales</span>
                </div>
                <div class="mobile-footer-item">
                    <span aria-hidden="true">🧾</span>
                    <span>Fuentes oficiales</span>
                </div>
            </div>
        </div>
    </div>
@endsection
