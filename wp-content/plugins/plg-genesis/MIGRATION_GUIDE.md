# 🔄 Guía de Migración de Roles y Permisos

## 📋 **Paso a Paso para Migrar Usuarios**

### **1. Acceder a la herramienta de migración**

Desde el dashboard v2, ve a:
```
http://tu-dominio.com/dashboard-v2/#/migration
```

O accede directamente a la URL:
- **Local:** `http://localhost:8080/dashboard-v2/#/migration`
- **Producción:** `https://emmausdigital.com/genesis/dashboard-v2/#/migration`

---

### **2. Primera vez: Hazte Super Admin**

Si no puedes acceder al dashboard porque no tienes permisos:

1. Click en **👑 Hacerme Super Admin**
2. Confirma la acción
3. Recarga la página

✅ Ahora tendrás acceso completo como Super Admin

---

### **3. Opciones de Migración**

#### **Opción A: Migración Automática (Recomendado)**

Para migrar todos los usuarios de una vez:

1. Click en **🤖 Migración Automática**
2. Revisa el mapeo de roles:
   - `administrator` → Super Admin
   - `editor` → Office Manager
   - `author/contributor` → Office Staff
   - `subscriber` → Office Viewer
3. Confirma
4. ✅ Todos los usuarios se migran automáticamente
5. Los usuarios sin oficina se asignan a **BOG** por defecto

#### **Opción B: Migración Individual (Control Total)**

Para migrar usuarios uno por uno:

1. En la tabla de "Usuarios que necesitan migración"
2. Click en **🔄 Migrar** junto al usuario
3. Selecciona:
   - **Nuevo Rol:** El rol del plugin que quieres asignar
   - **Oficina:** BOG, MED o CAL
4. Click en **✓ Migrar Usuario**
5. Repite para cada usuario

---

## 🎭 **Descripción de Roles**

| Rol | Permisos | Uso Recomendado |
|-----|----------|-----------------|
| **👑 Super Admin** | Acceso total, multi-oficina, selector de oficina | Director general, IT |
| **👨‍💼 Office Manager** | Admin completo de su oficina, gestión de usuarios | Coordinador de oficina |
| **👷 Office Staff** | CRUD de estudiantes/contactos/congresos, ver stats | Personal operativo |
| **👁️ Office Viewer** | Solo lectura de su oficina | Consultas, externos |

---

## 📊 **Ejemplo de Migración Típica**

### **Antes:**
```
juan@bog      → administrator  (rol WP)
maria@med     → editor         (rol WP)
carlos@bog    → author         (rol WP)
lucia@cal     → subscriber     (rol WP)
```

### **Después (Migración Automática):**
```
juan@bog      → plg_super_admin      + oficina: BOG
maria@med     → plg_office_manager   + oficina: MED
carlos@bog    → plg_office_staff     + oficina: BOG
lucia@cal     → plg_office_viewer    + oficina: CAL
```

---

## ⚠️ **Importante**

### **Antes de Migrar:**
- ✅ Haz backup de la base de datos
- ✅ Asegúrate de tener acceso administrator de WordPress
- ✅ Verifica que todos los usuarios tengan una oficina asignada

### **Durante la Migración:**
- Los usuarios **NO pierden acceso** a WordPress
- Los roles antiguos se **reemplazan** (no se acumulan)
- La migración es **reversible** (puedes reasignar roles después)

### **Después de Migrar:**
- ✅ Verifica que todos los usuarios aparecen en "Ya migrados"
- ✅ Prueba el acceso con diferentes usuarios
- ✅ Ajusta roles/oficinas si es necesario desde **Ajustes → Usuarios**

---

## 🗑️ **Limpieza Post-Migración**

Una vez completada la migración, elimina las herramientas temporales:

### **Archivos a eliminar:**
```bash
wp-content/plugins/plg-genesis/backend/api/controllers/MigrationController.php
wp-content/plugins/plg-genesis/frontendv2/pages/migration/roles.js
```

### **Código a eliminar de bootstrap.php:**
```php
// Líneas 182-186
require_once __DIR__ . '/api/controllers/MigrationController.php';
if (class_exists('PlgGenesis_MigrationController')) {
    PlgGenesis_MigrationController::register_routes();
}
```

### **Código a eliminar de router.js:**
```javascript
// Línea 68
if (hash.startsWith('#/migration')) return () => import('../pages/migration/roles.js')...
```

---

## 🆘 **Problemas Comunes**

### **No puedo acceder a la página de migración**
- Asegúrate de tener rol `administrator` en WordPress
- Usa el botón "Hacerme Super Admin" primero

### **Un usuario no aparece en la lista**
- Verifica que el usuario exista en wp-admin → Usuarios
- Recarga la página con el botón 🔄

### **Migración automática no funciona**
- Revisa la consola del navegador (F12) por errores
- Verifica que el usuario tenga `administrator` o `plg_switch_office`

### **Usuario migrado pero no ve el menú correcto**
- El usuario debe **cerrar sesión y volver a entrar**
- O hacer refresh forzado (Ctrl/Cmd + Shift + R)

---

## 📞 **Soporte**

Si tienes problemas con la migración:
1. Revisa los logs de PHP en el servidor
2. Verifica la tabla `wp_usermeta` (key: `oficina`)
3. Verifica la tabla `wp_users` (campo: `user_roles`)
4. Contacta al equipo de desarrollo

---

## ✅ **Checklist Final**

- [ ] Backup de base de datos realizado
- [ ] Acceso como administrator verificado
- [ ] Página de migración accesible
- [ ] Super Admin asignado (al menos 1 usuario)
- [ ] Todos los usuarios migrados
- [ ] Oficinas asignadas correctamente
- [ ] Acceso verificado con diferentes roles
- [ ] Archivos de migración eliminados
- [ ] Sistema de roles funcionando correctamente

---

**Fecha de creación:** 2025-01-10  
**Versión del sistema:** 1.0.0

