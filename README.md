Proyecto hackathon IA orientativa electoral

## Estructura

- **Laravel** (raíz del repo) — frontend / backend web, Vite + Tailwind
- **`services/rag-api`** — API FastAPI: RAG, embeddings, ChromaDB, LangChain
- **`resources/data/rules`** — reglas offline (`rules.json`, `output_format.json`)
- **`documents/`** — documentos para indexar en Chroma (montaje Docker del servicio RAG)
- **`.env`** — variables de entorno (Laravel en la raíz; el compose del RAG usa `../.env`)

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
  Requiere un archivo **`.env`** en la raíz (parte de `.env.example`). Monta `./documents` y `./services/rag-api`.
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

## Roles

Jaime → código en servicios Python (bajo `services/`)  
Emmanuel → RAG / Groq  
Tú → Laravel
