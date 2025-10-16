# Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [No publicado]

### Agregado

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

- **Lista de Estudiantes Responsive (patrón v1 exacto):**
  - Vista desktop (>= 1024px): tabla normal
  - Vista mobile/tablet (< 1024px): tabla transformada a tarjetas con CSS
  - Usa `data-label` en `<td>` para mostrar labels con `::before`
  - Diseño simple: label (110px, azul primario) + valor
  - Border-radius 8px, padding 0.75rem
  - Box-shadow sutil (0 1px 3px rgba 0.08)
  - Font-size label: 0.8rem, weight: 600
  - Sin duplicación de HTML, todo manejado por CSS
  - Réplica exacta del diseño v1

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
