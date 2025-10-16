# Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [No publicado]

### Corregido

- **Fix: Acordeones del menú mobile se cerraban al hacer click:**
  - El menú lateral completo se cerraba al hacer click en items con sub-menús
  - Impedía desplegar/colapsar los acordeones en mobile
  - Ahora solo cierra el menú al hacer click en sub-items (`.submenu`)
  - Los items principales abren/cierran sus acordeones correctamente

- **Fix: Iconos incorrectos en tabla de contactos mobile:**
  - Selector `.table:not(.users-table)` aplicaba iconos de estudiantes a contactos
  - Cambiado a `.table:not(.users-table):not(.contactos-table)` 
  - Ahora cada tabla tiene sus propios iconos correctos en mobile

### Agregado

- **Responsive Unificado para Todas las Listas (Estudiantes, Usuarios, Contactos):**
  - **Estrategia común:** Patrón tabla-a-tarjetas con `createTable` + `data-label`
  - **Implementación consistente en 3 páginas:**
    
    1. **Estudiantes (pages/estudiantes/list.js):** ✓ Ya implementado
       - Columnas: Código, Nombre, Documento, Celular, Email
       - Iconos: ID (gradiente azul), Nombre (destacado), 🆔 Documento, 📱 Celular, ✉️ Email
    
    2. **Usuarios (pages/users/list.js):** ✓ Refactorizado
       - Ahora usa `createTable` (antes renderizaba HTML manualmente)
       - Columnas: Usuario, Nombre, Email, Oficina, Rol, Acciones
       - Iconos: 👤 Usuario (gradiente azul), 📝 Nombre (destacado), ✉️ Email, 🏢 Oficina, 👤 Rol, ⚙️ Acciones
       - Clase específica: `.users-table`
    
    3. **Contactos (pages/contactos/list.js):** ✓ Mejorado
       - Ya usaba `createTable`, agregado data-labels
       - Columnas: ID, Nombre, Iglesia, Email
       - Iconos: ID (gradiente azul), Nombre (destacado), ⛪ Iglesia, ✉️ Email
       - Clase específica: `.contactos-table`
  
  - **CSS responsive compartido (styles/responsive.css):**
    - Estilos base en `.table` (aplican a todas)
    - Personalizaciones específicas con `.users-table` y `.contactos-table`
    - Iconos contextuales por tipo de tabla
    - Transformación automática < 1024px
    - Variables del tema 100%
  
  - **Resultado:**
    - Experiencia consistente en todas las listas
    - Mismo comportamiento en mobile/tablet
    - Código base compartido y mantenible
    - Fácil agregar nuevas tablas responsive

- **Nuevo Preset de Tema "emmausModal":**
  - Basado en la paleta de colores del modal "Cursos del Día"
  - Paleta de colores:
    - Accent (azul oscuro): #0c497a
    - Success (verde): #3fab49
    - Warning (naranja): #f59e0b
    - Danger (rojo): #e11d48
    - Info (azul): #3b82f6
    - Background: #f9fafb (gris muy claro)
    - Card Background: #ffffff (blanco)
    - Text: #1e293b (gris oscuro)
    - Muted Text: #64748b (gris medio)
    - Border: #e2e8f0 (gris claro)
    - Sidebar Background: #0a1224 (azul muy oscuro)
    - Sidebar Text: #f1f5f9 (gris muy claro)
  - Distribución visual limpia y moderna
  - Alto contraste y legibilidad
  - Disponible en página de personalización de tema

- **Eliminación de Colores Hardcodeados - Uso de Variables del Tema:**
  - **Componentes actualizados para usar variables CSS del tema:**
    - `components/ui/confirm.js`: Todos los colores ahora usan variables del tema
      - Overlay, modal, botones, títulos, textos
      - Gradientes dinámicos con color-mix() basados en --plg-danger, --plg-success, --plg-accent, --plg-warning
      - Sombras con color-mix() para transparencias
    - `pages/cursos/calendario.js`: 100% con variables del tema
      - Calendar grids, días, modales, botones
      - Gradientes en headers del modal
      - Estados hover con color-mix()
      - Notas de cursos usan --plg-success/--plg-danger
    - No más #hex, rgba() o colores literales hardcodeados
  - **Beneficios:**
    - Cambio de tema instantáneo en toda la aplicación
    - Consistencia visual garantizada
    - Personalización por oficina funcional
    - Fácil mantenimiento y extensión
    - Dark mode compatible en el futuro
  - **Técnica usada:** `color-mix(in srgb, var(--plg-color) X%, base)` para variaciones de color dinámicas

- **Sistema de Confirmación y Toasts Unificado:**
  - **Componente `confirm.js` reutilizable:** Reemplaza todos los `alert()` y `confirm()` nativos
  - Modal de confirmación personalizable con opciones:
    - title, message, confirmText, cancelText
    - icon (personalizable por contexto)
    - confirmClass: danger, success, primary, warning
    - Gradientes y colores según el tipo de acción
  - Diseño moderno con animaciones suaves (fadeIn, fadeOut, slideUp, bounce)
  - Backdrop blur(4px) para mejor enfoque
  - z-index: 10000 para estar sobre todo
  - Cierre con ESC, clic fuera o botón cancelar
  - Promise-based para async/await fácil
  
  - **Archivos actualizados:**
    - `pages/users/list.js`: Eliminación de usuarios, validaciones, CRUD
    - `pages/migration/roles.js`: Migraciones, hacerse admin
    - `pages/cursos/detail.js`: Eliminación de cursos
    - `pages/cursos/calendario.js`: Eliminación de cursos del calendario
    - `components/layout/menu.js`: Errores al cambiar oficina
  
  - **Toasts consistentes:**
    - ✓ Success (verde) para acciones exitosas
    - ⚠️ Warning (naranja) para validaciones
    - ❌ Error (rojo) para fallos
    - ℹ️ Info (azul) para información general
  
  - **Resultado:**
    - Experiencia de usuario profesional y consistente
    - Sin alerts/confirms nativos feos
    - Feedback visual claro y bonito
    - Confirmaciones claras antes de acciones destructivas

- **Página de Personalización de Tema Rediseñada:**
  - Header centrado con emoji 🎨 y descripción
  - Secciones organizadas por categoría (Colores Base, Sidebar/Menú, Estados y Feedback, Presets Rápidos)
  - Tarjetas de color interactivas:
    - Fondo con gradiente del color seleccionado (15%-25% opacidad)
    - Overlay blanco semitransparente (85%) para legibilidad
    - Borde dinámico con el color seleccionado (40% opacidad)
    - Color picker circular de 48px + input de texto monospace
    - Sincronización bidireccional automática picker↔input
    - Actualización en tiempo real del fondo al cambiar color
    - Hover elevado con transform translateY(-3px)
  - Campos con etiquetas uppercase, descripciones y tooltips
  - Grid responsive: auto-fill minmax(200px, 1fr)
  - Botones de acción con emojis: 👁️ Vista Previa, 💾 Guardar, 🔄 Restablecer
  - Presets con hover effect y feedback visual
  - Toasts en lugar de mensajes <pre>
  - Responsive: 1 columna en mobile, botones stacked

- **Sistema de Toasts/Notificaciones Mejorado:**
  - Diseño moderno y consistente con el tema
  - Colores del tema: `--plg-success` (verde), `--plg-warning` (naranja), `--plg-danger` (rojo), `--plg-accent` (azul)
  - Icono circular con fondo semitransparente (36px)
  - Border-radius 12px, sombras elevadas
  - Backdrop-filter blur(10px) para efecto glassmorphism
  - Animaciones suaves con cubic-bezier
  - Botón cerrar con hover effect (scale 1.1)
  - Responsive: full width en mobile con padding 10px
  - Auto-cierre configurable por tipo
  - Tipos: success, error, warning, info, forbidden

- **Sistema Responsive Dashboard v2:**
  - Archivo `frontendv2/styles/responsive.css` con breakpoints optimizados:
    - Mobile + Tablet: < 1024px (menú hamburguesa + tarjetas)
    - Desktop: >= 1024px (sidebar fijo + tabla)
  - Menú hamburguesa funcional con overlay y animaciones (z-index: 1001)
  - Sistema de tarjetas reutilizable para listas en mobile/tablet
  - Clases de utilidad responsive (`.show-mobile`, `.hide-mobile`, `.hide-desktop`)
  - Media queries para todos los componentes principales
  - Botón hamburguesa flotante con icono SVG
  - Toggle automático de menú al hacer clic en links (< 1024px)
  - Content con padding-top (70px) para evitar solapamiento con botón
  - Card con overflow:hidden para contener contenido

- **Lista de Estudiantes Responsive (patrón v1 con tema):**
  - Vista desktop (>= 1024px): tabla normal
  - Vista mobile/tablet (< 1024px): tabla transformada a tarjetas con CSS
  - Usa `data-label` en `<td>` para mostrar labels con `::before`
  - Código: gradiente con `var(--plg-accent)`, texto blanco
  - Nombre: fondo `color-mix` sutil con accent
  - Iconos: 🆔 Documento, 📱 Celular, ✉️ Email
  - Colores del tema: `--plg-border`, `--plg-cardBg`, `--plg-text`, `--plg-shadow`
  - Sin colores hardcoded, todo usa variables CSS del tema
  - Hover con elevación y transform

- **Calendario Responsive:**
  - Modal fullscreen en mobile
  - Grid de calendario adaptable (7 columnas compactas en mobile)
  - Tarjetas de cursos con layout vertical en mobile
  - Buscador responsive
  - Botones táctiles optimizados (min 44px)

- **Informe Anual Responsive:**
  - KPIs en 1 columna (mobile) y 2 columnas (tablet)
  - Tabs apilados verticalmente en mobile
  - Gráficas con altura adaptable (300px mobile, 350px tablet, 400px desktop)
  - Tabla con scroll horizontal en mobile
  - Controles de año en columna (mobile)

- **Informe Anual:**
  - Endpoint REST `GET /plg-genesis/v1/estadisticas/informe-anual?year=YYYY`
  - Página `frontendv2/pages/informes/informe-anual.js` con sistema de pestañas
  - Gráficas interactivas con Chart.js (Tendencias y Comparativa)
  - Nueva sección "Informes" en menú Dashboard v2

- **Calendario de Cursos:**
  - Endpoints REST:
    - `GET /plg-genesis/v1/cursos-calendario/mes?mes=1&anio=2025` (cursos por día del mes)
    - `GET /plg-genesis/v1/cursos-calendario/dia?dia=15&mes=1&anio=2025` (detalles de cursos del día)
    - `DELETE /plg-genesis/v1/estudiantes-cursos/{id}` (eliminar registro de curso)
  - Página `frontendv2/pages/cursos/calendario.js` con vista mensual
  - Modal de detalles con acciones: generar certificados y eliminar
  - Navegación entre meses
  - Integración en submenú "Cursos" del Dashboard v2

### Modificado

- **Layout Base:**
  - `dashboard.php`: agregado menú hamburguesa, overlay y script de toggle
  - Sidebar con transición y comportamiento overlay en mobile
  - Padding adaptable del content (16px mobile, 20px tablet, 24px desktop)

- `components.css`: agregados estilos responsive para grids, formularios y tablas
- Extendido `EstadisticasRepository` con método `getInformeAnual()` usando queries parametrizadas (pg_query_params)
- Extendido `EstadisticasService` con método `informeAnual()` con validación de año
- Extendido `EstadisticasController` con ruta `/estadisticas/informe-anual`

### Notas

- Dashboard v1 se mantiene sin cambios para compatibilidad
- Dashboard v2 ahora es 100% responsive en mobile, tablet y desktop
- Inputs con `font-size: 16px` en mobile para prevenir zoom en iOS
- Todos los botones tienen altura mínima de 44px para mejor accesibilidad táctil

## [1.0.0] - 2024-04-05

### Agregado

- Sistema completo de gestión de congresos
- Registro de estudiantes y asistentes externos
- Sistema de boletas para congresos con códigos de verificación
- Interfaz de búsqueda y visualización de congresos
- Detalle de inscritos por congreso
- Exportación a Excel de lista de inscritos
- Estadísticas de asistencia por congregación
- Gestión de talleres y asignaciones
- Sistema de migración de datos desde sistema anterior
- Registro público de asistentes externos
- Validación de boletas y códigos de verificación
- Sistema de registro de asistencia

### Características Principales

- Visualización de estadísticas de congresos
  - Total de asistentes
  - Desglose por estudiantes y asistentes externos
  - Estadísticas por congregación
  - Gráficos de distribución de asistentes
- Gestión de inscripciones
  - Registro de estudiantes
  - Registro de asistentes externos
  - Asignación de talleres
  - Generación de boletas
  - Validación de identificación duplicada
- Exportación de datos
  - Lista completa de inscritos en formato Excel
  - Incluye detalles como número de boleta, nombre, cédula, email, teléfono, congregación, taller y fecha de inscripción
  - Ordenamiento por fecha de inscripción y nombre

### Base de Datos

- Tablas principales implementadas:
  - `congresos`: Almacena información de los congresos
  - `asistencias_congresos`: Registra las asistencias
  - `boletas_congresos`: Gestiona las boletas y códigos de verificación
  - `estudiantes`: Información de estudiantes
  - `asistentes_externos`: Datos de asistentes externos
  - `contactos`: Información de contacto y congregaciones
  - `niveles`: Niveles educativos
  - `cursos`: Cursos disponibles
  - `programas`: Programas educativos
  - `estudiantes_cursos`: Relación estudiantes-cursos
  - `programas_cursos`: Relación programas-cursos
  - `programas_asignaciones`: Asignaciones de programas
  - `programas_prerequisitos`: Prerrequisitos entre programas

### Seguridad

- Autenticación de usuarios
- Validación de datos
- Protección contra SQL injection
- Manejo seguro de sesiones
- Validación de boletas y códigos
- Control de acceso por roles
- Registro de actividades

### Interfaz de Usuario

- Diseño responsivo con Bootstrap
- DataTables para visualización de datos
- Modales para detalles y acciones
- Formularios validados
- Mensajes de retroalimentación al usuario
- Interfaz de registro público
- Visualización de estadísticas
- Exportación de datos

### Migración

- Sistema de migración de datos desde sistema anterior
- Validación de datos migrados
- Registro de estado de migración
- Preservación de datos históricos

### API y Endpoints

- Registro público de asistentes
- Validación de boletas
- Obtención de estadísticas
- Gestión de inscripciones
- Exportación de datos
