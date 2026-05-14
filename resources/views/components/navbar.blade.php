@php
    $aiPalettePresets = config('ai_palettes.presets');
@endphp
<nav class="navbar">
    <div class="navbar-inner">
        <div class="navbar-left">
            <a href="{{ route('home') }}" class="navbar-logo">¿Y QUÉ HAGO?</a>
            <div class="navbar-links">
                <a href="{{ route('home') }}#como-funciona" class="navbar-link" data-active="{{ request()->routeIs('home') ? 'true' : 'false' }}">Cómo funciona</a>
                <a href="{{ route('home') }}#herramientas" class="navbar-link">Herramientas</a>
                <a href="{{ route('urgencia') }}" class="navbar-link" data-active="{{ request()->routeIs('urgencia') ? 'true' : 'false' }}">Urgente</a>
                <a href="{{ route('ia.dual') }}" class="navbar-link" data-active="{{ request()->routeIs('ia.dual') ? 'true' : 'false' }}">Comparador IA</a>
            </div>
        </div>
        <div class="navbar-cta">
            <div class="ai-palette-bar">
                <label for="ai-palette-select">Paleta</label>
                <select id="ai-palette-select" class="ai-palette-select" aria-label="Seleccionar paleta de colores">
                    @foreach ($aiPalettePresets as $id => $preset)
                        <option value="{{ $id }}">{{ $preset['label'] }}</option>
                    @endforeach
                    <option value="custom">PERSONALIZADA</option>
                </select>
            </div>
            <a href="{{ route('traductor') }}" class="btn btn-primary">
                Comenzar ahora
            </a>
        </div>
    </div>
    <div id="ai-palette-custom" class="ai-palette-custom ai-palette-custom--below" data-open="false">
        <p class="ai-palette-help u-text-muted">
            Ajusta los colores y pulsa «Aplicar». «Restablecer» borra la paleta personalizada guardada y devuelve los valores del formulario al preset OrientaVox.
        </p>
        <div class="ai-palette-custom-row">
            <label>Primario <input type="color" id="ai-custom-primary" value="#2F77E2" aria-label="Color primario personalizado"></label>
            <label>Secundario <input type="color" id="ai-custom-secondary" value="#1E56B8" aria-label="Color secundario personalizado"></label>
            <label>Acento <input type="color" id="ai-custom-accent" value="#429FF1" aria-label="Color de acento personalizado"></label>
            <label>Fondo <input type="color" id="ai-custom-bg" value="#E7EDFE" aria-label="Color de fondo personalizado"></label>
            <label>Texto <input type="color" id="ai-custom-text" value="#0F172A" aria-label="Color de texto personalizado"></label>
        </div>
        <div class="ai-palette-custom-actions">
            <button type="button" class="btn btn-primary" id="ai-palette-custom-apply" aria-label="Aplicar paleta personalizada">Aplicar</button>
            <button type="button" class="btn btn-secondary" id="ai-palette-custom-reset" aria-label="Restablecer colores del formulario a valores OrientaVox">Restablecer</button>
        </div>
    </div>
</nav>
