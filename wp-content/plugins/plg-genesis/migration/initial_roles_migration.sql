-- =====================================================
-- Migración Inicial de Roles y Permisos
-- =====================================================
-- Este script migra los usuarios de WordPress existentes
-- a los nuevos roles del plugin plg-genesis
--
-- IMPORTANTE: Ejecutar este script UNA SOLA VEZ después
-- de activar el plugin y antes de usar el dashboard-v2
--
-- Fecha: 2025-01-10
-- =====================================================

-- 1. Actualizar el usuario actual (daniel.vanegas) a Super Admin
-- Reemplaza 'daniel.vanegas' con tu usuario si es diferente
UPDATE edgen_usermeta 
SET meta_value = 'a:1:{s:16:"plg_super_admin";b:1;}'
WHERE user_id = (SELECT ID FROM edgen_users WHERE user_login = 'daniel.vanegas')
  AND meta_key = 'edgen_capabilities';

-- 2. Asignar oficina BOG al usuario si no tiene
INSERT INTO edgen_usermeta (user_id, meta_key, meta_value)
SELECT ID, 'oficina', 'BOG'
FROM edgen_users
WHERE user_login = 'daniel.vanegas'
  AND NOT EXISTS (
    SELECT 1 FROM edgen_usermeta 
    WHERE user_id = edgen_users.ID AND meta_key = 'oficina'
  );

-- =====================================================
-- OPCIONAL: Migración automática de todos los usuarios
-- =====================================================
-- Descomenta las siguientes líneas si quieres migrar
-- TODOS los usuarios automáticamente
--
-- Mapeo:
-- administrator -> plg_super_admin
-- editor        -> plg_office_manager
-- author        -> plg_office_staff
-- contributor   -> plg_office_staff
-- subscriber    -> plg_office_viewer
-- =====================================================

-- Migrar administrators a plg_super_admin
-- UPDATE edgen_usermeta 
-- SET meta_value = 'a:1:{s:16:"plg_super_admin";b:1;}'
-- WHERE meta_key = 'edgen_capabilities'
--   AND meta_value LIKE '%administrator%';

-- Migrar editors a plg_office_manager
-- UPDATE edgen_usermeta 
-- SET meta_value = 'a:1:{s:19:"plg_office_manager";b:1;}'
-- WHERE meta_key = 'edgen_capabilities'
--   AND meta_value LIKE '%editor%'
--   AND meta_value NOT LIKE '%administrator%';

-- Migrar authors/contributors a plg_office_staff
-- UPDATE edgen_usermeta 
-- SET meta_value = 'a:1:{s:17:"plg_office_staff";b:1;}'
-- WHERE meta_key = 'edgen_capabilities'
--   AND (meta_value LIKE '%author%' OR meta_value LIKE '%contributor%')
--   AND meta_value NOT LIKE '%administrator%'
--   AND meta_value NOT LIKE '%editor%';

-- Migrar subscribers a plg_office_viewer
-- UPDATE edgen_usermeta 
-- SET meta_value = 'a:1:{s:18:"plg_office_viewer";b:1;}'
-- WHERE meta_key = 'edgen_capabilities'
--   AND meta_value LIKE '%subscriber%'
--   AND meta_value NOT LIKE '%administrator%'
--   AND meta_value NOT LIKE '%editor%'
--   AND meta_value NOT LIKE '%author%'
--   AND meta_value NOT LIKE '%contributor%';

-- Asignar oficina BOG a todos los usuarios que no tienen oficina
-- INSERT INTO edgen_usermeta (user_id, meta_key, meta_value)
-- SELECT u.ID, 'oficina', 'BOG'
-- FROM edgen_users u
-- WHERE NOT EXISTS (
--   SELECT 1 FROM edgen_usermeta um 
--   WHERE um.user_id = u.ID AND um.meta_key = 'oficina'
-- );

-- =====================================================
-- Verificación: Ver los usuarios y sus nuevos roles
-- =====================================================
-- Ejecuta este query para verificar que todo quedó bien:
--
-- SELECT 
--   u.user_login,
--   u.user_email,
--   (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'edgen_capabilities') as roles,
--   (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'oficina') as oficina
-- FROM edgen_users u
-- ORDER BY u.user_login;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================

