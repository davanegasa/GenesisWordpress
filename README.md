# Sistema de Gestión de Estudiantes con WordPress y PostgreSQL

Este proyecto implementa un sistema de gestión de estudiantes utilizando WordPress como CMS, MySQL para WordPress y PostgreSQL para el plugin de gestión.

## Características

- Gestión de estudiantes
- Sistema de congresos con estados (PLANEACION, REGISTRO, EN_CURSO, FINALIZADO, CANCELADO)
- Registro de asistencias
- Generación de boletas
- Exportación de datos a Excel

## Requisitos

- Docker
- Docker Compose

## Instalación

1. Clonar el repositorio:

```bash
git clone https://github.com/tu-usuario/genesis-postgres.git
cd genesis-postgres
```

2. Configurar las bases de datos:

   - MySQL (WordPress):

     - Base de datos: wordpress
     - Usuario: wordpress
     - Contraseña: wordpress
     - Puerto: 3306

   - PostgreSQL (Plugin Genesis):
     - Base de datos: genesis
     - Usuario: genesis
     - Contraseña: genesis
     - Puerto: 5432

3. Iniciar los contenedores:

```bash
docker-compose up -d
```

4. Acceder a WordPress:

- URL: http://localhost:8080
- Usuario: admin
- Contraseña: (se genera durante la instalación)

5. Instalar el plugin Genesis:

- Subir el plugin desde el panel de administración de WordPress
- Activar el plugin

## Estructura del Proyecto

```
.
├── docker-compose.yml    # Configuración de Docker
├── wp-content/          # Contenido de WordPress
│   └── plugins/         # Plugins
│       └── plg-genesis/ # Plugin de gestión de estudiantes
│           └── migration/ # Migraciones de PostgreSQL
└── README.md            # Este archivo
```

## Desarrollo

Para desarrollo local:

1. Los cambios en el código del plugin se reflejan automáticamente gracias al volumen montado
2. Las bases de datos MySQL y PostgreSQL persisten entre reinicios
3. Para reiniciar los servicios:

```bash
docker-compose restart
```

4. Para acceder a las bases de datos:
   - MySQL: `mysql -h localhost -P 3306 -u wordpress -p`
   - PostgreSQL: `psql -h localhost -p 5432 -U genesis -d genesis`

## Configuración de DB_HOST en wp-config.php

Dependiendo del entorno donde ejecutes WordPress, debes ajustar el valor de `DB_HOST` en el archivo `wp-config.php`:

- **Desarrollo local con Docker Compose:**

  ```php
  define( 'DB_HOST', 'mysql' );
  ```

  Esto se debe a que el contenedor de WordPress se comunica con el contenedor de MySQL usando el nombre del servicio definido en `docker-compose.yml`.

- **Producción (o instalación tradicional):**
  ```php
  define( 'DB_HOST', 'localhost' );
  ```
  Usa `localhost` si la base de datos MySQL está en el mismo servidor que WordPress.

**Importante:**
Recuerda cambiar este valor según el entorno para evitar errores de conexión a la base de datos.

## Licencia

Este proyecto está bajo la licencia MIT.
