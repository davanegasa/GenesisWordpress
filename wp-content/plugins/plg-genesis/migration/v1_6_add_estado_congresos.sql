-- Agregar columna estado a la tabla congresos
ALTER TABLE congresos ADD COLUMN estado VARCHAR(20) DEFAULT 'PLANEACION';

-- Crear índice para mejorar el rendimiento de búsquedas por estado
CREATE INDEX idx_congresos_estado ON congresos(estado);

-- Agregar restricción para asegurar valores válidos
ALTER TABLE congresos ADD CONSTRAINT chk_estado_congreso 
CHECK (estado IN ('PLANEACION', 'REGISTRO', 'EN_CURSO', 'FINALIZADO', 'CANCELADO'));