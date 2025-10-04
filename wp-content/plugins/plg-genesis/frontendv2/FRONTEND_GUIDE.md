# Frontend v2 (API‑first) – Guía de arquitectura y convenciones

Esta guía documenta la arquitectura, estructura de directorios y patrones de implementación del frontend v2 ubicado en `wp-content/plugins/plg-genesis/frontendv2/`.

## Objetivos
- Coherencia visual en todas las páginas mediante tokens de tema (variables CSS)
- Navegación dentro de un dashboard único (shell) sin recargas
- Aislamiento del acceso a API y manejo uniforme de errores
- Componentes reutilizables (UI kit) y páginas desacopladas

## Estructura de directorios

```
frontendv2/
  core/               # Arranque, router, theme, utilidades DOM/estado
  api/                # Cliente HTTP (fetch)
  services/           # Servicios por dominio (consumen api/client)
  components/         # Componentes reutilizables
    layout/           # Shell del dashboard (Header, Sidebar, Layout)
    ui/               # Button, Input, Table, Card, Toast, etc.
    forms/            # Field, helpers de formularios
  pages/              # Páginas montables por router
    dashboard/
    estudiantes/
    contactos/
    congresos/
    settings/
  styles/             # tokens.css + capas base/components/utilities
  assets/             # icons, images
  FRONTEND_GUIDE.md   # Este documento
```

## Principios
- Un solo shell (`Layout`) con enrutamiento por hash (`#/ruta?query`)
- Sin colores hardcodeados. Todo estilo usa variables CSS declaradas en `styles/tokens.css`
- Páginas no hacen `fetch` directo; usan `services/*`
- UI kit común para estados `loading/error/empty`

## ThemeProvider (core/theme.js)
- Carga tema por oficina usando `GET /wp-json/plg-genesis/v1/theme`
- Aplica variables CSS a `:root` (`--plg-bg`, `--plg-text`, etc.)
- Expone funciones:
  - `loadTheme()` → Promise<{ bg, text, accent, sidebarBg, sidebarText, cardBg }>
  - `applyTheme(theme)` → side‑effect en `:root`
  - `saveTheme(theme)` → `PUT /theme`
  - `resetTheme()` → `DELETE /theme`

## Router (core/router.js)
- Maneja rutas tipo:
  - `#/dashboard`
  - `#/estudiantes`
  - `#/estudiante/:id`
  - `#/estudiantes/nuevo`
  - `#/contactos`
  - `#/congresos`
  - `#/tema`
- Cada página exporta `{ mount(container, params), unmount() }`
- Router hace lazy‑load (dynamic import) para performance

## Servicios (services/*)
- Devuelven `{ success, data }` o lanzan errores normalizados
- No manipulan DOM
- Ejemplo de firma:
  - `estudiantes.listarPorContacto(contactoId)`
  - `estudiantes.crear(payload)`
  - `estudiantes.obtener(id)`
  - `estudiantes.actualizar(id, payload)`

## UI Kit (components/ui)
- Componentes puros o funciones que devuelven nodos DOM
- Estilizados por `styles/components.css` y tokens de `styles/tokens.css`
- Estados: `disabled`, `loading`, `invalid`, `aria-*` y focus visible

## Estilos
- `styles/tokens.css` define todas las variables de tema y escalas
- `styles/base.css` añade reset, tipografía y helpers mínimos
- `styles/components.css` estiliza el UI kit
- `styles/utilities.css` incluye utilidades (espaciados, grid, flex, sombras)

### Utilidades base de espaciado y tipografía (homogeneidad)
- Clases de margen vertical:
  - `.u-mt-8`, `.u-mt-16`
  - `.u-mb-8`, `.u-mb-12`
- Títulos y secciones:
  - `.card-title` → margen inferior 12px
  - `.section` → separación superior 20px
  - `.section-title` → 16px, negrita, color `--plg-text`
- Inputs con estados: `.invalid` + `aria-invalid="true"`
- Toasts: usar helper `showToast(text, isError)` (no inventar variantes)

## Orden de carga
1. `core/bootstrap.js` → `loadTheme()` y `applyTheme()`
2. Monta `Layout` (Header/Sidebar/slot)
3. Inicia `router` y navega a la ruta actual

## Convenciones de nombres
- Archivos y rutas: kebab‑case
- Funciones/props: camelCase
- Constantes: SCREAMING_SNAKE_CASE

## Accesibilidad y responsive
- Focus visible consistente
- `aria-label` en iconos y botones sin texto
- Tablas con `scope="col"`, rol semántico en `Toast/Modal`
- Puntos de corte: `sm 640px`, `md 768px`, `lg 1024px`, `xl 1280px`

### Modal accesible (patrón recomendado)
1. Contenedor `overlay` a pantalla completa
2. Caja con `role="dialog"` y `aria-modal="true"`
3. Foco inicial a primer elemento interactivo
4. Cierre con `Escape` y trap de `Tab`

## Ejemplo mínimo de página

```js
// pages/dashboard/index.js
export function mount(container) {
  container.innerHTML = '<div class="card">Dashboard</div>';
}
export function unmount() { /* limpiar listeners si aplica */ }
```

## Roadmap de implementación
- [ ] ThemeProvider + tokens y base de estilos
- [ ] Layout + Router
- [ ] UI Kit (Button/Input/Card/Table/Toast)
- [ ] Dashboard KPIs (read-only)
- [ ] Estudiantes (list/create/detail)
- [ ] Contactos (búsqueda)
- [ ] Congresos (list)
- [ ] Settings/Tema (ya soportado por backend)