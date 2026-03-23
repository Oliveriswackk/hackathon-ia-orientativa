@extends('layouts.app')

@section('title', 'Orientación guiada · ¿Y qué hago?')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Prefijo orient- para no chocar con .chat-* del widget flotante en app.css */
        .orient-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 24px 20px 40px;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 220px);
        }

        .orient-header {
            margin-bottom: 16px;
        }

        .orient-title {
            font-size: clamp(20px, 3vw, 32px);
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--color-text);
        }

        .orient-subtitle {
            font-size: 14px;
            color: var(--color-text-muted);
            line-height: 1.6;
            max-width: 65ch;
        }

        .orient-counter {
            font-size: 12px;
            color: var(--color-primary);
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .orient-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 200px;
        }

        .orient-bubble {
            max-width: min(75%, 65ch);
            padding: 10px 14px;
            border-radius: 18px;
            line-height: 1.6;
            font-size: 15px;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .orient-bubble--user {
            align-self: flex-end;
            background: var(--color-primary);
            color: var(--color-on-primary);
            border-bottom-right-radius: 4px;
        }

        .orient-bubble--bot {
            align-self: flex-start;
            background: var(--color-surface-elevated);
            border: 1px solid var(--color-border-subtle);
            border-bottom-left-radius: 4px;
            color: var(--color-text);
        }

        .orient-input-area {
            display: flex;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid var(--color-border-subtle);
            flex-wrap: wrap;
            align-items: center;
        }

        .orient-input {
            flex: 1;
            min-width: 0;
            border-radius: 999px;
            border: 1px solid var(--color-border-strong);
            background: var(--color-surface);
            color: var(--color-text);
            padding: 10px 16px;
            font-size: 15px;
        }

        .orient-input::placeholder {
            color: var(--color-text-muted);
        }

        .orient-send-btn {
            padding: 10px 20px;
        }

        .orient-diagnostico {
            background: var(--color-surface-elevated);
            border-radius: 18px;
            padding: 18px;
            margin-top: 12px;
            border: 1px solid var(--color-border-subtle);
        }

        .orient-diagnostico-item {
            margin-bottom: 12px;
        }

        .orient-diagnostico-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-primary);
            margin-bottom: 4px;
        }

        .orient-diagnostico-value {
            font-size: 15px;
            font-weight: 500;
            line-height: 1.6;
            max-width: 65ch;
        }

        .orient-diagnostico-urgencia {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .orient-diagnostico-urgencia.alto {
            background: color-mix(in srgb, var(--color-secondary) 30%, var(--color-surface) 70%);
            color: var(--color-text);
        }

        .orient-diagnostico-urgencia.medio {
            background: color-mix(in srgb, var(--color-primary) 14%, var(--color-surface) 86%);
            color: var(--color-text);
            border: 1px solid var(--color-primary);
        }

        .orient-diagnostico-urgencia.bajo {
            background: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--color-border-subtle);
        }
    </style>
@endpush

@section('content')
    <div class="orient-container fade-up">
        <div class="orient-header">
            <h1 class="orient-title">Orientación guiada</h1>
            <p class="orient-subtitle">Responde algunas preguntas y te ayudamos a entender tu situación electoral</p>
            <div class="orient-counter" id="chatCounter">Pregunta 0 de 5</div>
        </div>

        <div class="orient-messages" id="chatMessages">
            <div class="orient-bubble orient-bubble--bot">
                Hola, soy ORIENTA. Te haré algunas preguntas para entender mejor tu situación electoral.
                Responde con tus propias palabras.
            </div>
        </div>

        <div class="orient-input-area">
            <input
                type="text"
                id="chatInput"
                class="orient-input"
                placeholder="Escribe tu respuesta aquí..."
                autocomplete="off"
                aria-label="Tu respuesta a la pregunta actual"
            >
            <button class="btn btn-primary orient-send-btn" type="button" id="chatSendBtn" aria-label="Enviar respuesta">
                Enviar
            </button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const chatInput = document.getElementById('chatInput');
        const chatSendBtn = document.getElementById('chatSendBtn');
        const chatMessages = document.getElementById('chatMessages');
        const chatCounter = document.getElementById('chatCounter');

        let historial = [];
        let preguntasRealizadas = 0;
        let esDiagnosticoFinal = false;

        function actualizarContador() {
            chatCounter.textContent = `Pregunta ${preguntasRealizadas} de 5`;
        }

        function agregarMensaje(texto, tipo) {
            const bubble = document.createElement('div');
            bubble.className = `orient-bubble orient-bubble--${tipo}`;
            bubble.textContent = texto;
            chatMessages.appendChild(bubble);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function mostrarDiagnostico(diagnostico) {
            const div = document.createElement('div');
            div.className = 'orient-diagnostico';

            const urgenciaClass = diagnostico.nivel_urgencia?.toLowerCase() || 'medio';

            div.innerHTML = `
                <div class="orient-diagnostico-item">
                    <div class="orient-diagnostico-label">Diagnóstico</div>
                    <div class="orient-diagnostico-value">${diagnostico.diagnostico || 'Consulta requerida'}</div>
                </div>
                <div class="orient-diagnostico-item">
                    <div class="orient-diagnostico-label">Ruta sugerida</div>
                    <div class="orient-diagnostico-value">${diagnostico.ruta_sugerida || 'Acude a la autoridad electoral'}</div>
                </div>
                <div class="orient-diagnostico-item">
                    <div class="orient-diagnostico-label">Nivel de urgencia</div>
                    <div class="orient-diagnostico-value">
                        <span class="orient-diagnostico-urgencia ${urgenciaClass}">
                            ${diagnostico.nivel_urgencia || 'MEDIO'}
                        </span>
                    </div>
                </div>
                <div class="orient-diagnostico-item">
                    <div class="orient-diagnostico-label">Acción inmediata</div>
                    <div class="orient-diagnostico-value">${diagnostico.accion_inmediata || 'Contacta a la autoridad electoral'}</div>
                </div>
            `;

            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function enviarMensaje() {
            const texto = chatInput.value.trim();
            if (!texto || esDiagnosticoFinal) return;

            agregarMensaje(texto, 'user');
            chatInput.value = '';
            chatInput.disabled = true;
            chatSendBtn.disabled = true;
            chatSendBtn.textContent = 'Enviando...';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const response = await fetch('/orientacion/mensaje', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        mensaje: texto,
                        historial: historial,
                        preguntas_realizadas: preguntasRealizadas,
                        _token: token,
                    }),
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Error al procesar el mensaje');
                }

                historial = data.historial || historial;
                preguntasRealizadas = data.preguntas_realizadas || preguntasRealizadas;
                esDiagnosticoFinal = data.es_diagnostico_final || false;

                actualizarContador();

                if (esDiagnosticoFinal && data.diagnostico) {
                    mostrarDiagnostico(data.diagnostico);
                    chatInput.placeholder = 'Ya completaste las 5 preguntas. Revisa el diagnóstico arriba.';
                } else {
                    agregarMensaje(data.respuesta, 'bot');
                }
            } catch (error) {
                agregarMensaje('Error de conexión. Intenta de nuevo.', 'bot');
            } finally {
                chatInput.disabled = false;
                chatSendBtn.disabled = false;
                chatSendBtn.textContent = 'Enviar';
                if (!esDiagnosticoFinal) {
                    chatInput.focus();
                }
            }
        }

        chatSendBtn.addEventListener('click', enviarMensaje);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                enviarMensaje();
            }
        });

        chatInput.focus();
    })();
</script>
@endpush
