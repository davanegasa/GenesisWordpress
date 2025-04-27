-- Actualizar URLs para desarrollo local
UPDATE `edgen_options` SET `option_value` = 'http://localhost:8081' WHERE `option_name` IN ('siteurl', 'home');

-- Limpiar cualquier caché de URLs
UPDATE `edgen_options` SET `option_value` = '' WHERE `option_name` = 'rewrite_rules'; 