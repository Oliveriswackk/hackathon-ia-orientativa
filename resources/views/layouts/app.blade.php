<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#E7EDFE">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <title>@yield('title', '¿Y qué hago?')</title>

    @php
        $aiPaletteMeta = config('ai_palettes.presets');
    @endphp
    {{-- Metadatos de paletas + aplicación inmediata desde localStorage (evita flash de color) --}}
    <script type="application/json" id="ai-palette-meta-data">{!! json_encode($aiPaletteMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script>
        (function () {
            try {
                var metaEl = document.getElementById('ai-palette-meta-data');
                var meta = metaEl ? JSON.parse(metaEl.textContent) : {};
                var key = localStorage.getItem('ai_palette') || 'orientavox';
                var root = document.documentElement.style;

                function applyVars(obj) {
                    if (!obj || typeof obj !== 'object') return;
                    for (var p in obj) {
                        if (Object.prototype.hasOwnProperty.call(obj, p) && p.indexOf('--') === 0) {
                            root.setProperty(p, obj[p]);
                        }
                    }
                }

                if (key === 'custom') {
                    var raw = localStorage.getItem('ai_custom_palette');
                    var custom = raw ? JSON.parse(raw) : null;
                    if (custom && typeof custom === 'object') {
                        applyVars(custom);
                    } else if (meta.orientavox && meta.orientavox.vars) {
                        applyVars(meta.orientavox.vars);
                    } else if (meta.koi && meta.koi.vars) {
                        applyVars(meta.koi.vars);
                    }
                } else {
                    var vars = (meta[key] && meta[key].vars) ? meta[key].vars : (meta.orientavox ? meta.orientavox.vars : (meta.koi ? meta.koi.vars : null));
                    applyVars(vars);
                }
            } catch (e) { /* silencioso: Vite aplicará de nuevo */ }
        })();
        (function () {
            try {
                if (localStorage.getItem('ai_accessible') === '1') {
                    document.documentElement.setAttribute('data-ai-accessible', 'true');
                }
            } catch (e) { /* sin localStorage */ }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --ov-lupin-url: url("{{ asset('images/lupin-mascot.png') }}");
        }
    </style>

    @stack('head')
</head>
<body>
    <div class="page-shell">
        @include('components.navbar')

        <main class="page-main">
            @yield('content')
        </main>

        <footer>
            <div class="footer-inner">
                <span><strong>¿Y qué hago?</strong> · Herramienta ciudadana para el Hackathon Ciberdemocracia 2026</span>
                <span>Esta plataforma es orientativa y no constituye asesoría legal ni sustituto de la autoridad electoral.</span>
            </div>
            <div class="footer-accessible">
                <button type="button" class="ai-accessible-toggle" id="ai-accessible-toggle">
                    <span class="ai-accessible-toggle-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                    <span class="ai-accessible-toggle-text">Modo accesible</span>
                </button>
            </div>
        </footer>

        @include('components.chatbot')
    </div>

    @stack('scripts')
</body>
</html>
