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
                    <a href="{{ route('onboarding.1') }}" class="mobile-icon-btn" aria-label="Ayuda e introducción">
                        <span aria-hidden="true">☰</span>
                    </a>
                </div>
            </header>
            <p class="ov-p" style="margin: -8px 0 16px; font-size: 13px; color: var(--color-text-muted);">
                ¿Y qué hago? · Herramienta ciudadana
            </p>

            <div class="ov-hero-icon" style="margin-bottom: 10px;">
                <img class="ov-lupin-img" src="{{ asset('images/lupin-mascot.png') }}" width="88" height="88" alt="Lupin, tu orientador">
            </div>

            <section class="mobile-hero">
                <h1>¿Recibiste un <span style="color: var(--color-primary);">documento oficial</span>?</h1>
                <p>Identifica rápidamente si requiere atención, qué implica y cuánto tiempo tienes para actuar.</p>
            </section>

            <a href="{{ route('orientacion') }}" class="action-card action-card-primary" aria-label="Revisar documento">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">📄</span>
                    <div>
                        <div class="action-card-title">Revisar documento</div>
                        <div class="action-card-subtitle">Escanear, subir PDF o describir lo que recibiste</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('urgencia') }}" class="action-card action-card-danger" aria-label="Orientación urgente">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">⚡</span>
                    <div>
                        <div class="action-card-title" style="color: var(--color-secondary);">Orientación urgente</div>
                        <div class="action-card-subtitle">Necesito actuar rápido</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('orientacion') }}" class="action-card" aria-label="Tengo una situación">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">💬</span>
                    <div>
                        <div class="action-card-title">Tengo una situación</div>
                        <div class="action-card-subtitle">Cuéntame qué pasó y te guío paso a paso</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('salida') }}" class="action-card" aria-label="Probar demo">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">🧪</span>
                    <div>
                        <div class="action-card-title">¿Quieres ver un ejemplo?</div>
                        <div class="action-card-subtitle">Probar demo con resultado orientativo</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <a href="{{ route('traductor') }}" class="action-card" aria-label="Traducir documento">
                <div class="action-card-left">
                    <span class="action-card-icon" aria-hidden="true">📝</span>
                    <div>
                        <div class="action-card-title">Pegar texto del documento</div>
                        <div class="action-card-subtitle">Pégalo y te lo explico en lenguaje claro</div>
                    </div>
                </div>
                <span class="action-card-chevron" aria-hidden="true">›</span>
            </a>

            <div class="mobile-footer-bar" aria-label="Barra informativa">
                <div class="mobile-footer-item">
                    <span aria-hidden="true">🔒</span>
                    <span>Información cifrada y sin guardar datos sensibles</span>
                </div>
                <div class="mobile-footer-item">
                    <span aria-hidden="true">🧾</span>
                    <span>Fuentes oficiales</span>
                </div>
            </div>
        </div>
    </div>
@endsection
