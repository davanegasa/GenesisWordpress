# 🚀 Migración Rápida de Roles (SQL)

## ⚡ Opción Rápida: Script SQL

En lugar de usar la interfaz web, puedes ejecutar este script SQL directamente en la base de datos.

### 📝 **Paso a Paso:**

#### **1. Acceder a la base de datos**

**Opción A: phpMyAdmin (recomendado para producción)**
- Ir a: `https://tu-dominio.com:2083` (cPanel)
- Click en "phpMyAdmin"
- Seleccionar base de datos: `emmaus_wpgenesis`

**Opción B: Línea de comandos (local/Docker)**
```bash
docker exec -it genesiswordpress-mariadb-1 mysql -u emmaus_wpgenesis -pemmaus_wpgenesis emmaus_wpgenesis
```

**Opción C: Adminer/DBeaver/TablePlus**
- Host: `localhost` (o tu servidor)
- Usuario: `emmaus_wpgenesis`
- Password: `emmaus_wpgenesis`
- Base de datos: `emmaus_wpgenesis`

---

#### **2. Ejecutar el script SQL**

Abre el archivo:
```
wp-content/plugins/plg-genesis/migration/initial_roles_migration.sql
```

Copia y pega **SOLO las primeras líneas** (migración de tu usuario):

```sql
-- Actualizar tu usuario a Super Admin
UPDATE edgen_usermeta 
SET meta_value = 'a:1:{s:15:"plg_super_admin";b:1;}'
WHERE user_id = (SELECT ID FROM edgen_users WHERE user_login = 'daniel.vanegas')
  AND meta_key = 'edgen_capabilities';

-- Asignar oficina BOG
INSERT INTO edgen_usermeta (user_id, meta_key, meta_value)
SELECT ID, 'oficina', 'BOG'
FROM edgen_users
WHERE user_login = 'daniel.vanegas'
  AND NOT EXISTS (
    SELECT 1 FROM edgen_usermeta 
    WHERE user_id = edgen_users.ID AND meta_key = 'oficina'
  );
```

⚠️ **IMPORTANTE:** Reemplaza `'daniel.vanegas'` con tu nombre de usuario de WordPress.

---

#### **3. Verificar**

Ejecuta este query para verificar:

```sql
SELECT 
  u.user_login,
  u.user_email,
  (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'edgen_capabilities') as roles,
  (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'oficina') as oficina
FROM edgen_users u
WHERE u.user_login = 'daniel.vanegas';
```

Deberías ver:
- **roles:** `a:1:{s:15:"plg_super_admin";b:1;}`
- **oficina:** `BOG`

---

#### **4. Probar en el dashboard**

1. Ve a: `http://localhost:8080/dashboard-v2/`
2. Recarga la página (Ctrl/Cmd + Shift + R)
3. ✅ Ahora deberías tener acceso total como Super Admin
4. Verás el selector de oficina en la parte superior
5. Todos los menús estarán visibles

---

## 🔄 **Migración de Todos los Usuarios (Opcional)**

Si quieres migrar **TODOS** los usuarios automáticamente:

1. Abre el archivo `initial_roles_migration.sql`
2. **Descomenta** las líneas de la sección "OPCIONAL"
3. Ejecuta el script completo
4. ✅ Todos los usuarios se migrarán según el mapeo:
   - `administrator` → Super Admin
   - `editor` → Office Manager
   - `author/contributor` → Office Staff
   - `subscriber` → Office Viewer

---

## ✅ **Ventajas de este método:**

- ⚡ Rápido (1 segundo vs minutos en UI)
- 🎯 Directo (sin dependencias de permisos)
- 🔒 Seguro (puedes hacer backup antes)
- 📝 Auditable (script versionado en Git)

---

## ❓ **Problemas Comunes**

### Error: "Unknown column"
- Verifica que el prefijo de tablas sea correcto (`edgen_` en este caso)
- Si tu WordPress usa otro prefijo, reemplázalo en el script

### No veo cambios en el dashboard
- Cierra sesión y vuelve a entrar
- O recarga con Ctrl/Cmd + Shift + R

### Usuario no encontrado
- Verifica tu nombre de usuario:
  ```sql
  SELECT user_login FROM edgen_users;
  ```

---

**Fecha:** 2025-01-10  
**Versión:** 1.0.0

