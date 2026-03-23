@extends('layouts.app')

@section('title', '¿Y qué hago? · Orientación electoral para jóvenes')

@push('head')
    <style>
        .welcome-hero {
            text-align: center;
            padding: 60px 20px 80px;
            max-width: 900px;
            margin: 0 auto;
        }

        .welcome-title {
            font-size: clamp(28px, 5vw, 48px);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 16px;
            color: var(--color-primary);
        }

        .welcome-subtitle {
            font-size: clamp(18px, 2.5vw, 22px);
            color: var(--color-text-muted);
            margin-bottom: 48px;
            line-height: 1.6;
            max-width: 65ch;
            margin-left: auto;
            margin-right: auto;
        }

        .welcome-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: color-mix(in srgb, var(--color-surface) 92%, transparent);
            border-radius: 16px;
            border: 1px solid var(--color-border-subtle);
            padding: 32px 24px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, border-color 0.2s ease-out, opacity 0.2s ease-out;
            text-decoration: none;
            display: block;
            color: inherit;
            box-shadow: 0 2px 8px var(--color-shadow-soft);
        }

        .welcome-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 12px color-mix(in srgb, var(--color-accent) 28%, var(--color-shadow-soft) 72%);
            border-color: color-mix(in srgb, var(--color-accent) 45%, var(--color-border-subtle) 55%);
        }

        .welcome-card-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .welcome-card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--color-primary);
        }

        .welcome-card-desc {
            font-size: 15px;
            color: var(--color-text-muted);
            line-height: 1.6;
            max-width: 65ch;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
@endpush

@section('content')
    <div class="welcome-hero fade-up">
        <h1 class="welcome-title">¿Y qué hago?</h1>
        <p class="welcome-subtitle">
            Orientación electoral para jóvenes. Gratis. En menos de 90 segundos.
        </p>

        <div class="welcome-cards">
            <a href="{{ route('traductor') }}" class="welcome-card">
                <div class="welcome-card-icon">📄</div>
                <div class="welcome-card-title">Tengo un documento</div>
                <div class="welcome-card-desc">
                    Pega tu acuerdo o resolución y te explicamos los plazos y qué hacer
                </div>
            </a>

            <a href="{{ route('orientacion') }}" class="welcome-card">
                <div class="welcome-card-icon">💬</div>
                <div class="welcome-card-title">Tengo una situación</div>
                <div class="welcome-card-desc">
                    Describe con tus palabras y te guiamos paso a paso
                </div>
            </a>

            <a href="{{ route('urgencia') }}" class="welcome-card">
                <div class="welcome-card-icon">⚡</div>
                <div class="welcome-card-title">Es urgente</div>
                <div class="welcome-card-desc">
                    Respuesta directa, sin pasos. Acción inmediata
                </div>
            </a>

            <a href="{{ route('ia.dual') }}" class="welcome-card">
                <div class="welcome-card-icon">⚖️</div>
                <div class="welcome-card-title">Comparador IA</div>
                <div class="welcome-card-desc">
                    Groq en línea y Phi‑3 local, misma pregunta en dos columnas
                </div>
            </a>
        </div>
    </div>
@endsection
