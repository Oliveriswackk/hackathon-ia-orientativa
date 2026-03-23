Proyecto hackathon IA orientativa electoral

## Estructura

- **Laravel** (raíz del repo) — frontend / backend web, Vite + Tailwind
- **`services/rag-api`** — API FastAPI: RAG, embeddings, ChromaDB, LangChain
- **`resources/data/rules`** — reglas offline (`rules.json`, `output_format.json`)
- **`documents/`** — documentos para indexar en Chroma (montaje Docker del servicio RAG)
- **`.env`** — variables de entorno (Laravel en la raíz; el compose del RAG usa `../.env`)

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
