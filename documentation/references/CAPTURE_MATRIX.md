# Matriz diseño (Proyectos.pdf / OrientaVox) → rutas Laravel

Referencia versionada: [Proyectos.pdf](./Proyectos.pdf)

| Orden PDF | Pantalla | Ruta | Blade |
|-----------|-----------|------|--------|
| 1 | Términos + Continuar | `/onboarding/2` | `resources/views/onboarding-2.blade.php` |
| 2 | Hub inicio | `/` | `resources/views/welcome.blade.php` |
| 3 | Emergencia · Paso 1 | `/urgencia` | `resources/views/urgencia.blade.php` |
| 4 | Paso 2 · documento | `/urgencia` (paso 2) | idem |
| 5 | Procesando | `/procesando` | `resources/views/procesando.blade.php` |
| 6 | Salida estructurada | `/salida` | `resources/views/salida.blade.php` |
| 7 | Paso 3 · texto libre | `/urgencia?step=3` o paso 3 del wizard | `urgencia.blade.php` |
| — | Situación · paso 2 (doc) | `/orientacion` | `resources/views/orientacion.blade.php` |
| — | Comparador IA | `/ia` | `resources/views/ia-dual.blade.php` |
| — | Traductor | `/traductor` | `resources/views/traductor.blade.php` |

## Copy corregido respecto al PDF

- TEPFJ → **TEPJF**
- “casa” (PDF) → **caso** en copy de orientación/urgencia
- Ortografía: ningún, asesoría, acentos en títulos públicos

## Marca en UI

- Flujo móvil usa **OrientaVox** como nombre visible; subtítulo o pie puede mantener **¿Y qué hago?** / hackathon según producto.
