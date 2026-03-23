@extends('layouts.app')

@section('title', 'Modo urgencia · ¿Y qué hago?')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .urgencia-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .urgencia-title {
            font-size: clamp(20px, 3vw, 32px);
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }

        .urgencia-subtitle {
            font-size: 16px;
            color: var(--color-text-muted);
            text-align: center;
            margin-bottom: 32px;
            line-height: 1.6;
            max-width: 65ch;
            margin-left: auto;
            margin-right: auto;
        }

        .urgencia-input {
            width: 100%;
            border-radius: 18px;
            border: 1px solid var(--color-border-strong);
            background: var(--color-surface);
            color: var(--color-text);
            padding: 16px 18px;
            font-size: 16px;
            min-height: 120px;
            resize: vertical;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .urgencia-input::placeholder {
            color: var(--color-text-muted);
        }

        .urgencia-btn {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            background: var(--color-secondary);
            color: var(--color-on-primary);
            border: none;
            border-radius: 999px;
            cursor: pointer;
            transition:
                transform 0.18s ease-out,
                box-shadow 0.18s ease-out,
                opacity 0.18s ease-out;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--color-secondary) 35%, var(--color-shadow-soft) 65%);
        }

        .urgencia-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 14px color-mix(in srgb, var(--color-secondary) 45%, transparent);
        }

        .urgencia-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .urgencia-result {
            margin-top: 24px;
            background: var(--color-surface-elevated);
            border-radius: 18px;
            padding: 20px;
            border: 1px solid var(--color-border-subtle);
            display: none;
        }

        .urgencia-result.show {
            display: block;
        }

        .urgencia-result-item {
            margin-bottom: 16px;
        }

        .urgencia-result-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-primary);
            margin-bottom: 6px;
        }

        .urgencia-result-value {
            font-size: 16px;
            font-weight: 500;
        }

        .urgencia-result-link {
            color: var(--color-primary);
            text-decoration: underline;
        }

        .urgencia-fallback {
            background: color-mix(in srgb, var(--color-accent) 18%, var(--color-surface) 82%);
            border: 1px solid var(--color-border-subtle);
            border-radius: 14px;
            padding: 14px;
            margin-top: 16px;
            font-size: 14px;
            color: var(--color-text);
            line-height: 1.6;
            max-width: 65ch;
        }
    </style>
@endpush

@section('content')
    <div class="urgencia-container fade-up">
        <h1 class="urgencia-title">Es urgente</h1>
        <p class="urgencia-subtitle">Describe brevemente tu situación y te diremos qué hacer ahora mismo</p>

        <textarea 
            id="urgenciaInput" 
            class="urgencia-input" 
            placeholder="Describe brevemente tu situación..."
        ></textarea>

        <button class="urgencia-btn" type="button" id="urgenciaBtn">
            Necesito ayuda ahora
        </button>

        <div class="urgencia-result" id="urgenciaResult">
            <div class="urgencia-result-item">
                <div class="urgencia-result-label">Acción inmediata</div>
                <div class="urgencia-result-value" id="resultAccion"></div>
            </div>
            <div class="urgencia-result-item">
                <div class="urgencia-result-label">Autoridad</div>
                <div class="urgencia-result-value" id="resultAutoridad"></div>
            </div>
            <div class="urgencia-result-item">
                <div class="urgencia-result-label">Link oficial</div>
                <div class="urgencia-result-value">
                    <a href="#" target="_blank" class="urgencia-result-link" id="resultLink"></a>
                </div>
            </div>
        </div>

        <div class="urgencia-fallback" id="urgenciaFallback" style="display: none;">
            <strong>Si tarda más de 5 segundos:</strong> Llama al tribunal electoral de tu estado o acude en persona lo antes posible.
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const urgenciaInput = document.getElementById('urgenciaInput');
        const urgenciaBtn = document.getElementById('urgenciaBtn');
        const urgenciaResult = document.getElementById('urgenciaResult');
        const urgenciaFallback = document.getElementById('urgenciaFallback');
        const resultAccion = document.getElementById('resultAccion');
        const resultAutoridad = document.getElementById('resultAutoridad');
        const resultLink = document.getElementById('resultLink');

        let fallbackTimeout;

        async function consultar() {
            const situacion = urgenciaInput.value.trim();
            if (!situacion) {
                alert('Describe tu situación para poder ayudarte');
                return;
            }

            urgenciaBtn.disabled = true;
            urgenciaBtn.textContent = 'Consultando...';
            urgenciaResult.classList.remove('show');
            urgenciaFallback.style.display = 'none';

            // Mostrar fallback después de 5 segundos
            fallbackTimeout = setTimeout(() => {
                urgenciaFallback.style.display = 'block';
            }, 5000);

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('/urgencia/consultar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        situacion: situacion,
                        _token: token
                    })
                });

                clearTimeout(fallbackTimeout);
                urgenciaFallback.style.display = 'none';

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Error al procesar la consulta');
                }

                resultAccion.textContent = data.accion_inmediata || 'Contacta a la autoridad electoral';
                resultAutoridad.textContent = data.autoridad || 'Autoridad electoral competente';
                resultLink.href = data.link || 'https://tepjf.gob.mx';
                resultLink.textContent = data.link || 'https://tepjf.gob.mx';

                urgenciaResult.classList.add('show');

            } catch (error) {
                clearTimeout(fallbackTimeout);
                urgenciaFallback.style.display = 'block';
                resultAccion.textContent = 'Llama al tribunal electoral de tu estado o acude en persona lo antes posible.';
                resultAutoridad.textContent = 'Tribunal Electoral de tu estado o IEE local';
                resultLink.href = 'https://tepjf.gob.mx';
                resultLink.textContent = 'https://tepjf.gob.mx';
                urgenciaResult.classList.add('show');
            } finally {
                urgenciaBtn.disabled = false;
                urgenciaBtn.textContent = 'Necesito ayuda ahora';
            }
        }

        urgenciaBtn.addEventListener('click', consultar);
        urgenciaInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                consultar();
            }
        });
    })();
</script>
@endpush
