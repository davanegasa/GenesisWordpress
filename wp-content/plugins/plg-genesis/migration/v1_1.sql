-- Crear tabla de asistentes externos con id_contacto
CREATE TABLE IF NOT EXISTS asistentes_externos (
    id SERIAL PRIMARY KEY,
    id_contacto INT NOT NULL REFERENCES contactos(id),
    nombre VARCHAR(255) NOT NULL,
    identificacion VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    congregacion VARCHAR(255)
);

-- Crear tabla de congresos
CREATE TABLE IF NOT EXISTS congresos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    ubicacion VARCHAR(255)
);

-- Crear tabla de asistencias a congresos
CREATE TABLE IF NOT EXISTS asistencias_congresos (
    id SERIAL PRIMARY KEY,
    id_estudiante INT NULL REFERENCES estudiantes(id),
    id_asistente INT NULL REFERENCES asistentes_externos(id),
    id_congreso INT REFERENCES congresos(id),
    taller_asignado VARCHAR(255),
    asistencia BOOLEAN DEFAULT FALSE
);

CREATE TABLE boletas_congresos (
    id SERIAL PRIMARY KEY,
    numero_boleta VARCHAR(10) UNIQUE NOT NULL,  -- '001' hasta '720'
    codigo_verificacion VARCHAR(4) NOT NULL,    -- Código de 4 dígitos
    id_congreso INT REFERENCES congresos(id) ON DELETE CASCADE, -- Congreso al que pertenece
    id_asistente INT REFERENCES asistentes_externos(id) ON DELETE SET NULL, -- Asistente registrado
    id_estudiante INT REFERENCES estudiantes(id) ON DELETE SET NULL, -- Estudiante registrado
    id_asistencia INT REFERENCES asistencias_congresos(id) ON DELETE SET NULL, -- Asistencia registrada
    estado VARCHAR(10) DEFAULT 'activo' CHECK (estado IN ('activo', 'usado')) -- Estado de la boleta
);