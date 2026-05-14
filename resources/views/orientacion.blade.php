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

            <span class="ov-flow-eyebrow">Situación</span>
            <span class="ov-step-pill">Paso 2 de 3</span>
            <h1 class="ov-h2" style="margin-top: 0; color: var(--color-secondary);">¿Tienes el documento contigo?</h1>
            <p class="ov-p">Esto nos ayuda a entender tu <strong>caso</strong> más rápido y con mayor precisión.</p>

            <div class="ov-card-shell">
                <button type="button" class="action-card" id="ov-orient-scan" style="width: 100%; border: none; cursor: pointer; text-align: left;">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">📷</span>
                        <div>
                            <div class="action-card-title">Escanear con cámara</div>
                            <div class="action-card-subtitle">Toma una foto al documento</div>
                        </div>
                    </div>
                </button>

                <label class="action-card" style="margin-top: 12px; cursor: pointer; display: flex;">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">📄</span>
                        <div>
                            <div class="action-card-title">Subir archivo</div>
                            <div class="action-card-subtitle">Selecciona un archivo PDF</div>
                        </div>
                    </div>
                    <input type="file" id="ov-orient-file" accept=".pdf,application/pdf" class="sr-only">
                </label>

                <a class="action-card" href="{{ route('urgencia') }}?step=3" style="margin-top: 12px;">
                    <div class="action-card-left">
                        <span class="action-card-icon" aria-hidden="true">✍️</span>
                        <div>
                            <div class="action-card-title">No, describir la situación</div>
                            <div class="action-card-subtitle">Escribe lo que entiendas del documento</div>
                        </div>
                    </div>
                    <span class="action-card-chevron" aria-hidden="true">›</span>
                </a>

                <button type="button" class="ov-foot-link" id="ov-orient-doc-help">¿Qué documentos puedo subir?</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const scan = document.getElementById('ov-orient-scan');
        const file = document.getElementById('ov-orient-file');
        const docHelp = document.getElementById('ov-orient-doc-help');

        scan?.addEventListener('click', () => {
            alert('La cámara en el navegador se integrará en una siguiente versión. Por ahora puedes subir un PDF o describir tu situación.');
        });

        file?.addEventListener('change', () => {
            if (file.files?.length) {
                try {
                    sessionStorage.setItem('ov_last_emergency', 'Archivo PDF seleccionado (demo).');
                } catch (e) {}
                window.location.href = @json(route('procesando'));
            }
        });

        docHelp?.addEventListener('click', () => {
            alert('Puedes subir un PDF legible. No guardamos el archivo en servidores en esta demo local.');
        });
    })();
</script>
@endpush
