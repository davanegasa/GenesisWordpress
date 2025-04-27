# Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
