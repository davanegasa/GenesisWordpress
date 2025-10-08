
ALTER TABLE cursos
ADD COLUMN created_at TIMESTAMP DEFAULT now(),
ADD COLUMN updated_at TIMESTAMP DEFAULT now(),
ADD COLUMN deleted_at TIMESTAMP;

UPDATE cursos SET created_at = now() WHERE created_at IS NULL;
UPDATE cursos SET updated_at = now() WHERE updated_at IS NULL;

CREATE INDEX cursos_deleted_active_idx ON cursos (id) WHERE deleted_at IS NULL;
