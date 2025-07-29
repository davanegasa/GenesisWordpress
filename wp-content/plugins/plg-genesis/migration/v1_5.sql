-- Crear tabla de permisos de usuario
CREATE TABLE IF NOT EXISTS user_permissions (
    id SERIAL PRIMARY KEY,
    wp_user_id TEXT NOT NULL,
    permission TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT now(),
    CONSTRAINT user_permissions_wp_user_id_permission_key UNIQUE (wp_user_id, permission)
);

-- Otorgar permisos
GRANT ALL ON TABLE user_permissions TO emmaus_admin;
GRANT ALL ON TABLE user_permissions TO emmaus_estudiantes;
GRANT ALL ON SEQUENCE user_permissions_id_seq TO emmaus_admin; 