@extends('layouts.app')

@section('title', 'Orientación guiada · ¿Y qué hago?')

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

            <h1 class="ov-h2" style="margin-top: 52px;">Situación</h1>
            <p class="ov-p">Paso 2 de 3</p>

            <div class="ov-card" style="margin-top: 10px;">
                <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 900;">¿Tienes el documento contigo?</h2>
                <p class="ov-p" style="margin-bottom: 14px;">Esto nos ayuda a entender tu caso más rápido y con mayor precisión.</p>

                <a class="action-card" href="{{ route('traductor') }}">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">📷</span>
                        <div>
                            <div class="action-card-title">Escanear con cámara</div>
                            <div class="action-card-subtitle">Toma una foto al documento</div>
                        </div>
                    </div>
                    <span class="action-card-chevron" aria-hidden="true">›</span>
                </a>

                <a class="action-card" href="{{ route('traductor') }}" style="margin-top: 12px;">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">📄</span>
                        <div>
                            <div class="action-card-title">Subir archivo</div>
                            <div class="action-card-subtitle">Selecciona un archivo PDF</div>
                        </div>
                    </div>
                    <span class="action-card-chevron" aria-hidden="true">›</span>
                </a>

                <a class="action-card" href="{{ route('orientacion') }}" style="margin-top: 12px;">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">✍️</span>
                        <div>
                            <div class="action-card-title">No, describir la situación</div>
                            <div class="action-card-subtitle">Escribe lo que entiendas del documento</div>
                        </div>
                    </div>
                    <span class="action-card-chevron" aria-hidden="true">›</span>
                </a>

                <div style="margin-top: 14px; text-align: center; font-size: 13px; color: var(--color-primary);">
                    ¿Qué documentos puedo subir?
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
@endpush
