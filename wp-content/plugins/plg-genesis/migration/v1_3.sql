-- Crear tabla de observaciones de contactos
CREATE TABLE IF NOT EXISTS observaciones_contactos (
    id SERIAL PRIMARY KEY,
    contacto_id INTEGER NOT NULL REFERENCES contactos(id) ON DELETE CASCADE,
    observacion TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT now(),
    usuario_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    tipo VARCHAR(10) NOT NULL DEFAULT 'General',
    usuario_nombre VARCHAR(255)
);

-- Crear índice para mejorar el rendimiento de las consultas
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE indexname = 'idx_observaciones_contacto_id') THEN
        CREATE INDEX idx_observaciones_contacto_id ON observaciones_contactos(contacto_id);
    END IF;
END $$;

-- Otorgar permisos
GRANT ALL ON TABLE observaciones_contactos TO emmaus_admin;
GRANT ALL ON TABLE observaciones_contactos TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE observaciones_contactos_id_seq TO emmaus_admin; 