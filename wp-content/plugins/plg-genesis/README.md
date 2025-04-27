# Sistema de Gestión de Congresos - Genesis

Sistema de gestión de congresos para el Instituto Bíblico Genesis, desarrollado como un plugin de WordPress.

## Descripción

Este sistema permite la gestión completa de congresos, incluyendo registro de estudiantes y asistentes externos, generación de boletas con códigos de verificación, visualización de estadísticas y exportación de datos.

## Características Principales

- Gestión de congresos y talleres
- Registro de estudiantes y asistentes externos
- Sistema de boletas con códigos de verificación
- Estadísticas de asistencia por congregación
- Exportación de datos a Excel
- Interfaz de registro público
- Sistema de migración de datos

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Extensión PHP para PostgreSQL

## Instalación

1. Clonar el repositorio en la carpeta `wp-content/plugins/` de tu instalación de WordPress:

   ```
   git clone https://github.com/tu-usuario/plg-genesis.git wp-content/plugins/plg-genesis
   ```

2. Activar el plugin desde el panel de administración de WordPress.

3. Configurar la conexión a la base de datos PostgreSQL en el archivo de configuración.

4. Ejecutar las migraciones de la base de datos:
   ```
   php migration/run.php
   ```

## Estructura del Proyecto

```
plg-genesis/
├── assets/           # Archivos CSS, JS e imágenes
├── backend/          # Lógica del servidor y API
├── frontend/         # Interfaz de usuario
├── migration/        # Scripts de migración de base de datos
├── CHANGELOG.md      # Registro de cambios
├── README.md         # Documentación principal
└── .gitignore        # Archivos ignorados por Git
```

## Uso

### Administración de Congresos

1. Acceder a la sección "Congresos" en el menú de WordPress.
2. Crear un nuevo congreso con nombre, fecha y ubicación.
3. Gestionar talleres y asignaciones.
4. Ver estadísticas de asistencia.

### Registro de Asistentes

1. Acceder a la página de registro público.
2. Ingresar datos del asistente.
3. Validar boleta y código de verificación.
4. Asignar taller si corresponde.

### Exportación de Datos

1. Acceder al detalle de inscritos de un congreso.
2. Hacer clic en el botón "Descargar Excel".
3. El archivo se descargará con todos los datos de los inscritos.

## Contribución

1. Fork del repositorio
2. Crear una rama para tu característica (`git checkout -b feature/nueva-caracteristica`)
3. Commit de tus cambios (`git commit -am 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Crear un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## Contacto

Para cualquier consulta o soporte, contactar al equipo de desarrollo.
