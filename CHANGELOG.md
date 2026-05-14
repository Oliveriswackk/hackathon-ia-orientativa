# Changelog

Registro de cambios del proyecto **hackathon-ia-orientativa-dev** (Laravel + RAG + PWA / modo offline).

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/). Las versiones semánticas (`0.x.y`) se usarán cuando etiquetes releases en Git.

## [Unreleased]

### Documentación y orden del repositorio

- **README** con mapa del monorepo: Laravel en raíz, `services/rag-api` (FastAPI + RAG/Chroma), `documents/` como corpus del RAG (solo `.gitkeep` en git; PDFs locales ignorados), `resources/data/offline` y `resources/data/rules`, Docker Compose.
- **`LISTADO_CARPETAS_PROYECTO.txt`**: guía en texto de qué es cada carpeta raíz y para qué sirve (complemento al README).
- **`documentation/references/`**: referencias de diseño (p. ej. matriz PDF ↔ rutas en `CAPTURE_MATRIX.md`; PDF de proyecto cuando se copie aquí para versionarlo — no usar `docs/` en raíz, está en `.gitignore`).
- Convención **una sola carpeta de corpus**: `documents/` canónica; `docs/` ignorada para evitar duplicados con documentación ad-hoc.

### Infraestructura y entorno

- **`docker-compose.yml`** y **`docker/`** para servicios (Chroma, `rag-api`, perfil opcional Laravel en contenedor).
- **`certificates/cacert.pem`**: bundle CA para HTTPS saliente cuando Laravel lo use (ver `AppServiceProvider`).
- **`config/ai_palettes.php`**: presets de tema por variables CSS.
- **`.env.example`**, **`.env.testing`**, scripts en **`scripts/`** y catálogo generado en `public/catalog.json` vía build cuando aplique.

### Motor offline y datos

- Módulos en **`resources/js/offline/`** (catálogo, normalización de texto, composición de orientación, formato de salida) y datos en **`resources/data/offline/`** (casos JSON / esquema).
- Reglas de ejemplo en **`resources/data/rules/`** (`rules.json`, `output_format.json`).

### Frontend — flujo tipo OrientaVox (responsive)

- **`resources/css/app.css`**: tokens `--color-*`, layout `.page-shell` / `.page-container`, flujo móvil `.ov-*` (pantalla, fondo, shell, pasos, tarjetas de elección, chips, botones), comparador dual (`.dual-ia-*`), traductor (`.translator-*`), utilidades (`.ov-muted`, `.ov-chip-*`).
- **Fondo `.ov-bg`**: base casi blanca (`#E7EDFE` vía paleta) con elipses suaves **visibles dentro del viewport** (gradientes, sin halos fuera de pantalla).
- **Paleta ORIENTAVOX** por defecto (`#2F77E2`, `#429FF1`, `#E7EDFE`, texto oscuro, superficie blanca); selector en navbar + script anti-parpadeo en `layouts/app.blade.php` y `resources/js/palette.js`.
- **`layouts/app.blade.php`**: `--ov-lupin-url` para mascota; `theme-color` y **PWA** (`manifest.webmanifest`) alineados al fondo claro.
- **`components/navbar.blade.php`**: enlaces, paleta personalizada con valores por defecto OrientaVox.

### Vistas Blade (rutas en `routes/web.php`)

- **`welcome.blade.php`**: hub “¿Recibiste un documento oficial?”, CTAs (revisar, urgencia, situación, demo, traductor), pie de privacidad; mascota Lupin.
- **Onboarding** (`onboarding-1` … `onboarding-3`): flujo intro, términos con checkbox único, pantalla final; marca OrientaVox + subtítulo “¿Y qué hago?”.
- **`urgencia.blade.php`**: asistente en **3 pasos** (situación → documento con escaneo/subir/describir → texto); aviso de cámara “próxima versión”; PDF → `procesando`.
- **`orientacion.blade.php`**: paso 2 alineado al PDF (mismas acciones que urgencia: cámara con aviso, subir PDF, describir → `urgencia?step=3`).
- **`procesando.blade.php`**: mensaje empático, barra y lista de estados, redirección a salida; Lupin.
- **`salida.blade.php`**: resultado estructurado (plazo, tipo de documento, **TEPJF**, secciones tipo guía, links, chips); **Escuchar** con `speechSynthesis` (voz del navegador).
- **`traductor.blade.php`**: layout responsive unificado (`translator-ov-page`), análisis vía API.
- **`ia-dual.blade.php`**: envoltorio `dual-ia-ov-page`, intro “Herramientas / Comparador de modelos”; tabs móviles (comportamiento en `resources/js/chat.js`).

### Assets

- **`public/images/lupin-mascot.png`**: mascota provisional; uso con `{{ asset('images/lupin-mascot.png') }}` en vistas clave.
- **`public/mockups/`** (si están presentes): referencias visuales secundarias.

### Corregido / afinado

- Copy respecto al PDF de referencia (p. ej. **TEPJF**, “caso” en lugar de “casa”).
- Enlace roto en orientación (“describir”) sustituido por flujo correcto hacia urgencia paso 3.
- Visibilidad de Lupin y fondo; rutas de imagen robustas con `asset()`.

### Mantenimiento del repo

- Eliminada entrada duplicada de **`.DS_Store`** en `.gitignore`.
- Eliminado del disco el artefacto **`.phpunit.result.cache`** (caché de PHPUnit; ya listado en `.gitignore` — no debe versionarse).

---

### Nota sobre “archivos basura”

Lo que **suele** no subirse a Git (y ya está ignorado o es generado): `vendor/`, `node_modules/`, `public/build/`, `public/hot`, `.env`, bases `*.sqlite`, logs, `.idea/` / `.vscode/` si el equipo no los versiona, cachés PHPUnit. **`LISTADO_CARPETAS_PROYECTO.txt`** no es basura: es documentación; si prefieres moverlo a `documentation/` puedes hacerlo en un commit aparte.

**Cómo seguir:** con cada entrega añade viñetas bajo `[Unreleased]`. Para cerrar versión, renombra el bloque a `## [0.1.0] - YYYY-MM-DD` y deja arriba un `[Unreleased]` vacío o con lo nuevo.
