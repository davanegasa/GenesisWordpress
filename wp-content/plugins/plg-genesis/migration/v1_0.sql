-- Tabla de Usuarios
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tabla de Contactos
CREATE TABLE contactos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    iglesia VARCHAR(100),
    email VARCHAR(100),
    celular VARCHAR(20),
    direccion VARCHAR(255),
    ciudad VARCHAR(50),
    code CHAR(10),
    fecha_registro TIMESTAMP DEFAULT now()
);

-- Tabla de Estudiantes
CREATE TABLE estudiantes (
    id SERIAL PRIMARY KEY,
    id_contacto INTEGER,
    doc_identidad VARCHAR(15),
    id_estudiante VARCHAR(50),
    nombre1 VARCHAR(50),
    nombre2 VARCHAR(50),
    apellido1 VARCHAR(50),
    apellido2 VARCHAR(50),
    celular VARCHAR(20),
    email VARCHAR(100),
    ciudad VARCHAR(50),
    iglesia VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT now(),
    CONSTRAINT fk_estudiantes_contacto FOREIGN KEY (id_contacto) REFERENCES contactos(id) ON DELETE SET NULL
);

-- Tabla de Niveles
CREATE TABLE niveles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Tabla de Cursos
CREATE TABLE cursos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    descripcion TEXT,
    id_material VARCHAR(20),
    id_tipo VARCHAR(20),
    valor_costo NUMERIC,
    valor_venta NUMERIC,
    consecutivo INTEGER,
    nivel_id INTEGER,
    CONSTRAINT fk_cursos_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE SET NULL
);

-- Tabla de Relación Estudiantes - Cursos
CREATE TABLE estudiantes_cursos (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER,
    curso_id INTEGER,
    fecha DATE,
    porcentaje DOUBLE PRECISION,
    enviado BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_estudiantes_cursos_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE SET NULL,
    CONSTRAINT fk_estudiantes_cursos_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
);

-- Tabla de Programas
CREATE TABLE programas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

-- Tabla de Relación Programas - Cursos
CREATE TABLE programas_cursos (
    id SERIAL PRIMARY KEY,
    programa_id INTEGER NOT NULL,
    curso_id INTEGER NOT NULL,
    consecutivo INTEGER NOT NULL,
    CONSTRAINT fk_programas_cursos_programa FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE SET NULL,
    CONSTRAINT fk_programas_cursos_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
);

-- Tabla de Asignaciones de Programas
CREATE TABLE programas_asignaciones (
    id SERIAL PRIMARY KEY,
    programa_id INTEGER NOT NULL,
    estudiante_id INTEGER,
    contacto_id INTEGER,
    fecha_asignacion TIMESTAMP DEFAULT now(),
    CONSTRAINT fk_programas_asignaciones_programa FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE SET NULL,
    CONSTRAINT fk_programas_asignaciones_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE SET NULL,
    CONSTRAINT fk_programas_asignaciones_contacto FOREIGN KEY (contacto_id) REFERENCES contactos(id) ON DELETE SET NULL,
    CONSTRAINT chk_uno_o_otro CHECK (
        ((estudiante_id IS NOT NULL) AND (contacto_id IS NULL)) OR 
        ((contacto_id IS NOT NULL) AND (estudiante_id IS NULL))
    )
);

-- Tabla de Prerrequisitos entre Programas
CREATE TABLE programas_prerequisitos (
    id SERIAL PRIMARY KEY,
    programa_id INTEGER NOT NULL,
    prerequisito_id INTEGER NOT NULL,
    CONSTRAINT fk_programas_prerequisitos_programa FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE SET NULL,
    CONSTRAINT fk_programas_prerequisitos_prerequisito FOREIGN KEY (prerequisito_id) REFERENCES programas(id) ON DELETE SET NULL
);

-- Tabla de Observaciones de Estudiantes
CREATE TABLE observaciones_estudiantes (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL,
    observacion TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT now(),
    usuario_id INTEGER,
    tipo VARCHAR(10) NOT NULL DEFAULT 'General',
    CONSTRAINT fk_observaciones_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    CONSTRAINT fk_observaciones_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Otorgar permisos a emmaus_bo_admin
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO emmaus_bo_admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO emmaus_bo_admin;
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO emmaus_bo_admin;

-- Aplicar permisos automáticamente a futuras tablas y secuencias
ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT ALL PRIVILEGES ON TABLES TO emmaus_bo_admin;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT ALL PRIVILEGES ON SEQUENCES TO emmaus_bo_admin;
