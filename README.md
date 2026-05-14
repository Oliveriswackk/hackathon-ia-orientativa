Proyecto hackathon IA orientativa electoral

## Estructura del repositorio

Monorepo: **Laravel** (app web en la raíz), **`services/rag-api`** (FastAPI + RAG + Chroma), **`documents/`** (corpus para indexar, montado en Docker), **`resources/data/`** (JSON offline y contrato de salida). Variables de entorno: **`.env`** en la raíz (Laravel y `docker compose` del RAG leen desde ahí).

**Nota:** la carpeta `docs/` en la raíz está en `.gitignore` (no se versiona); no confundir con `documents/`, que es el corpus del RAG.

### Árbol principal (resumen)

```
.
├── app/                    # PHP: controladores, modelos, servicios (IA, Groq, Ollama…)
├── bootstrap/              # Arranque Laravel
├── certificates/           # cacert.pem (CA bundle; ver AppServiceProvider)
├── config/                 # Configuración Laravel (services, DB, cola…)
├── database/               # Migraciones, factories, seeders (SQLite local ignorado por git)
├── docker/                 # Dockerfile opcional para Laravel en contenedor (perfil with-php)
├── documents/              # PDFs y corpus para Chroma (solo .gitkeep en git; PDFs locales)
├── public/                 # index.php, assets compilados (build/), mockups PNG
├── resources/
│   ├── css/                # Estilos (Tailwind / Vite)
│   ├── js/                 # Frontend: chat, offline-engine, PWA
│   ├── views/              # Blade: pantallas, layouts, componentes
│   └── data/
│       ├── offline/        # Catálogo JSON modo avión (cases/, schema.json)
│       └── rules/          # rules.json, output_format.json
├── routes/                 # web.php, api.php, console.php
├── services/rag-api/       # Python FastAPI: rutas, core, servicios Chroma/RAG/LLM
│   └── scripts/            # Scripts de desarrollo (p. ej. comprobar Chroma)
├── storage/                # Logs, caché, sesiones (runtime)
├── tests/                  # PHPUnit Feature / Unit
├── vendor/, node_modules/  # Dependencias (no editar)
├── docker-compose.yml      # Chroma + rag-api (+ laravel-web opcional)
├── composer.json, package.json, vite.config.js
└── .env.example            # Plantilla de variables
```

### Qué hay en cada parte (mapa rápido)

- **`app/`** — Lógica de negocio PHP: `Http/Controllers`, `Models`, `Services` (incluye integración IA y `AI/`), `Contracts`. Aquí enlazas rutas con comportamiento y proveedores externos.
- **`routes/`** — Definición de URLs web y API y comandos Artisan.
- **`config/`** — Ajustes por entorno (`services.php`, `database.php`, colas, etc.).
- **`database/`** — Esquema y datos de prueba. `*.sqlite` no se versiona (`database/.gitignore`).
- **`resources/views/`** — Plantillas Blade (onboarding, orientación, salida, componentes).
- **`resources/js/`** — JavaScript del cliente; incluye motor offline contra JSON embebido en el build.
- **`resources/css/`** — Hoja de estilos principal.
- **`resources/data/offline/`** — Casos y `schema.json` para el asistente sin red.
- **`resources/data/rules/`** — Ejemplo de reglas y contrato `output_format.json`.
- **`public/`** — Punto de entrada HTTP; `build/` generado por Vite; `mockups/` referencias de diseño. `public/hot` solo en desarrollo (ignorado por git).
- **`tests/`** — Suite `php artisan test` (usa `.env.testing`).
- **`certificates/`** — Bundle Mozilla para HTTPS saliente cuando está presente.
- **`docker/`** — Imagen PHP para servicio `laravel-web` en Compose.
- **`documents/`** — Corpus para el RAG. En el repositorio se mantiene la carpeta con **`documents/.gitkeep`**; los PDF se añaden en local y **no** se suben por defecto (`.gitignore`: `documents/*` con excepción de `.gitkeep`). Docker monta esta ruta en `rag-api` solo lectura.
- **`services/rag-api/`** — API Python: `main.py`, `app/api/routes/`, `app/core/` (interfaces/DTOs), `app/services/` (Chroma, RAG, LLM, lectura de archivos). `scripts/` contiene utilidades manuales de desarrollo.

## Modos de IA (online / offline)

La app está pensada para ser **accesible en móvil (PWA)** y funcionar con y sin Internet.

- **Online (con Internet)**: usa Groq (LLM) vía el backend Laravel.
- **Offline (sin Internet)**: **NO usa LLM** (ni Groq ni Ollama). Usa un **asistente determinístico** basado en JSON precargado. Esto evita depender de dispositivos extra o instalaciones locales de modelos.

### Qué significa “offline”

- En offline la app **solo puede responder lo que esté precargado** en JSON.
- Si una consulta no existe en el catálogo offline, la respuesta debe ser algo como: “No tengo esa información en modo offline. Conéctate a Internet para consultarlo.”

### Consultas offline (seed inicial sugerido)

Estas son categorías/casos recomendados para arrancar el catálogo offline (puedes ajustar a tus necesidades reales):

- **Plazos y medios de impugnación**: cuándo empieza a correr el plazo, cuántos días, consecuencias fuera de plazo.
- **Qué hacer ante notificaciones** (p. ej. cédula de notificación): pasos inmediatos, autoridad, ruta sugerida.
- **Dónde presentar un escrito/medio**: autoridad responsable vs tribunal competente (según el caso).
- **Requisitos básicos de un escrito** (checklist).
- **Cómo ubicar la autoridad competente** (guía por estado/instancia si está precargada).
- **Glosario** de términos comunes (plazo, notificación, acto impugnable, etc.).

> Nota: el repo ya trae un ejemplo de reglas en `resources/data/rules/rules.json` y un “contrato” de salida en `resources/data/rules/output_format.json`.

### Cómo usar JSON para el modo offline

Hoy existen:
- `resources/data/rules/rules.json`: ejemplo de un caso/reglas.
- `resources/data/rules/output_format.json`: formato objetivo para respuestas estructuradas.
- `resources/data/offline/schema.json`: esquema del catálogo offline.
- `resources/data/offline/cases/*.json`: catálogo de casos offline (seed inicial).

Casos incluidos (seed actual):
- `impugnacion-cedula-notificacion`
- `glosario-basico`
- `ubicar-autoridad`
- `consultar-en-linea-recomendacion`

Recomendación de estructura para escalar:
- Mantener el catálogo en `resources/data/offline/`:
  - `resources/data/offline/cases/*.json` (casos)
  - `resources/data/offline/schema.json` (esquema)

Cada “caso” offline típicamente incluye:
- `id`, `titulo`, `keywords`
- `preguntas` (wizard para aclarar)
- `respuesta` (plantilla)
- `fuentes` (links oficiales o referencias precargadas)

### Cómo agregar una consulta offline nueva

1. Crea un archivo JSON nuevo en `resources/data/offline/cases/mi-caso.json` (copia uno existente).
2. Agrega keywords útiles (sin acentos y con variantes si aplica).
3. Si hay desambiguación, agrega `questions` con opciones (se mostrarán como chips).
4. Llena `output` siguiendo el shape de `resources/data/rules/output_format.json`.
5. Ejecuta `npm run build` y prueba en modo avión.

### Cómo probar el modo offline

- Instala/abre la PWA normalmente (con Internet) para que cachee su “app shell”.
- Activa modo avión / desactiva datos.
- La UI debe cambiar a **modo offline (limitado)** y permitir consultas solo del catálogo JSON.

## Arranque rápido

- Laravel en tu máquina: `composer run dev` o `php artisan serve` + `npm run dev`
- RAG + Chroma (desde la **raíz** del repo): `docker compose up -d`  
  Requiere un archivo **`.env`** en la raíz (parte de `.env.example`). Monta `./documents` y `./services/rag-api`. Los PDF del corpus van en **`documents/`** en local (en git solo se mantiene la carpeta vía `documents/.gitkeep`; ver `.gitignore`).
- Alternativa: dentro de `services/rag-api`, `docker compose up` (mismos puertos; rutas relativas `../.env` y `../documents`).
- **Laravel en Docker (opcional):** `docker compose --profile with-php up -d`  
  Servicio **`laravel-web`** en [http://127.0.0.1:8080](http://127.0.0.1:8080). La imagen incluye PHP + Composer, no Node: compila assets en el host con `npm run build` antes (o monta `public/build` generado). Primera migración SQLite, por ejemplo:  
  `docker compose --profile with-php exec laravel-web php artisan migrate --force`

### Docker: proceso de `build` y arranque

1. **`.env` en la raíz** (no versionado): copia desde `.env.example` y genera clave Laravel si hace falta:
   `copy .env.example .env` → `php artisan key:generate`
2. **Construir imágenes** (RAG + imagen PHP opcional):
   - Solo API RAG: `docker compose build rag-api`
   - Incluir Laravel en contenedor: `docker compose build rag-api laravel-web`
   - Todo lo definido en el compose: `docker compose build`
3. **Levantar**:
   - RAG + Chroma: `docker compose up -d`
   - Añadir Laravel (8080): `docker compose --profile with-php up -d`
4. **Comprobar**: Chroma `http://127.0.0.1:8001/api/v1/heartbeat`, RAG `http://127.0.0.1:8000/docs`, Laravel `http://127.0.0.1:8080`

Si `docker compose build` falla por red o permisos, ejecuta Docker Desktop (o el daemon) y repite el build.

### Variables de entorno (resumen)

Detalle comentado en **`.env.example`**.

| Variable | Quién la lee | Uso |
|----------|----------------|-----|
| `APP_KEY`, `APP_URL`, `DB_*`, sesión, cola, etc. | Laravel | App web y APIs bajo `/api`. |
| `GROQ_API_KEY` | Laravel (`GroqService`, `LlamaProvider`) y **`rag-api`** (LangChain) | Misma clave para ambos; obligatoria para LLM en el servicio Python. |
| `CHROMA_HOST`, `CHROMA_PORT` | `rag-api` (`ChromaImpl`) | En **docker compose** ya vienen en el servicio (`chromadb` + puerto interno `8000`). Solo las defines en `.env` si corres FastAPI/Chroma en local sin compose (típico: host `localhost`, puerto host `8001`). |

**Ollama desde el contenedor Laravel:** `OllamaProvider` y `OllamaService` leen **`OLLAMA_URL`** y **`OLLAMA_MODEL`** desde `config/services.php` (`.env`). En el host, el default es `http://127.0.0.1:11434`. El servicio **`laravel-web`** fija en Compose `OLLAMA_URL=http://host.docker.internal:11434` para que no pise el `.env` pensado para PHP en tu máquina. Para otra URL u Ollama en red Docker, usa `docker-compose.override.yml` o edita ese `environment`. Si hay timeout, comprueba que Ollama escuche en `0.0.0.0:11434`.

## Tests

`php artisan test` usa `APP_ENV=testing` y el archivo **`.env.testing`** (versionado), así que no hace falta un `.env` local para la suite.



