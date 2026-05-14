@extends('layouts.app')

@section('title', 'Comparador IA · Groq y Offline · ¿Y qué hago?')

@section('content')
    <div class="dual-ia-ov-page">
        <div class="page-container dual-ia-page">
        <div id="dual-ia-root" class="dual-ia" data-api-base="{{ url('/api') }}" data-offline-first-groq="false">
            <header class="dual-ia-intro">
                <span class="ov-flow-eyebrow" style="margin-top: 0;">Herramientas</span>
                <h1 class="dual-ia-title">Comparador de modelos</h1>
                <p class="dual-ia-lead">
                    Misma pregunta para <strong>Groq</strong> (en línea) y para el modo <strong>Offline</strong> (sin Internet, basado en JSON precargado).
                    Puedes enviar a ambos o solo a uno. En offline la respuesta es limitada al catálogo precargado.
                </p>
            </header>

            <div class="dual-ia-mobile-tabs" role="tablist" aria-label="Modelo visible en pantalla pequeña">
                <button
                    type="button"
                    class="dual-ia-tab"
                    role="tab"
                    id="dual-tab-groq"
                    aria-selected="true"
                    aria-controls="dual-panel-groq"
                    data-tab-target="groq"
                    aria-label="Mostrar columna Groq en línea"
                >
                    Groq (línea)
                </button>
                <button
                    type="button"
                    class="dual-ia-tab"
                    role="tab"
                    id="dual-tab-ollama"
                    aria-selected="false"
                    aria-controls="dual-panel-ollama"
                    data-tab-target="ollama"
                    aria-label="Mostrar columna Offline"
                >
                    Offline (JSON)
                </button>
            </div>

            <div class="dual-ia-cols">
                <section
                    class="dual-ia-col ai-col-groq is-mobile-active"
                    id="dual-panel-groq"
                    role="tabpanel"
                    aria-labelledby="dual-col-groq-title"
                    data-col="groq"
                >
                    <div class="dual-ia-col-head">
                        <span class="dual-ia-col-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M22 10a3 3 0 0 0-3-3h-2.207a5.502 5.502 0 0 0-10.702.5"/></svg>
                        </span>
                        <div class="dual-ia-col-titles">
                            <h2 class="dual-ia-col-title" id="dual-col-groq-title">Groq · LLaMA</h2>
                            <p class="dual-ia-col-sub">Modelo en la nube (API)</p>
                        </div>
                        <div class="dual-ia-status" id="dual-status-groq" data-connected="false">
                            <span class="dual-ia-status-dot" aria-hidden="true"></span>
                            <span class="dual-ia-status-label">Comprobando…</span>
                        </div>
                    </div>
                    <div class="dual-ia-messages" id="dual-msgs-groq" aria-live="polite" aria-relevant="additions"></div>
                </section>

                <section
                    class="dual-ia-col ai-col-ollama"
                    id="dual-panel-ollama"
                    role="tabpanel"
                    aria-labelledby="dual-col-ollama-title"
                    data-col="ollama"
                >
                    <div class="dual-ia-col-head">
                        <span class="dual-ia-col-icon dual-ia-col-icon--server" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
                        </span>
                        <div class="dual-ia-col-titles">
                            <h2 class="dual-ia-col-title" id="dual-col-ollama-title">Offline · Catálogo</h2>
                            <p class="dual-ia-col-sub">Asistente guiado (sin LLM)</p>
                        </div>
                        <div class="dual-ia-status" id="dual-status-ollama" data-connected="false">
                            <span class="dual-ia-status-dot" aria-hidden="true"></span>
                            <span class="dual-ia-status-label">Comprobando…</span>
                        </div>
                    </div>
                    <div class="dual-ia-messages" id="dual-msgs-ollama" aria-live="polite" aria-relevant="additions"></div>
                </section>
            </div>

            <form class="dual-ia-input-bar" id="dual-ia-form" novalidate>
                <label class="sr-only" for="dual-ia-input">Escribe tu pregunta para los modelos</label>
                <textarea
                    id="dual-ia-input"
                    class="dual-ia-textarea"
                    name="question"
                    rows="2"
                    maxlength="16000"
                    required
                    placeholder="Escribe tu pregunta electoral u orientación…"
                    aria-describedby="dual-ia-target-hint"
                ></textarea>

                <div class="dual-ia-controls">
                    <fieldset class="dual-ia-target" id="dual-ia-target-hint">
                        <legend class="dual-ia-target-legend">Enviar a</legend>
                        <label class="dual-ia-radio-label">
                            <input type="radio" name="dual-send-target" value="both" checked>
                            Ambos
                        </label>
                        <label class="dual-ia-radio-label">
                            <input type="radio" name="dual-send-target" value="groq">
                            Solo Groq
                        </label>
                        <label class="dual-ia-radio-label">
                            <input type="radio" name="dual-send-target" value="ollama">
                            Solo Offline
                        </label>
                    </fieldset>
                    <button type="submit" class="btn btn-primary dual-ia-send" id="dual-ia-submit" aria-label="Enviar pregunta a los modelos seleccionados">
                        Enviar
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection
