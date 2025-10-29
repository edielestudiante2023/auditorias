# Guía de Pruebas Manuales - Sistema de Filtros por Rol

## Archivos Implementados

### Filtros
- `app/Filters/AuthFilter.php` - Verifica sesión activa
- `app/Filters/RoleFilter.php` - Verifica roles permitidos

### Helper
- `app/Helpers/auth_helper.php` - Funciones auxiliares de autenticación

### Configuración
- `app/Config/Filters.php` - Registro de filtros y CSRF habilitado
- `app/Config/Routes.php` - Grupos de rutas protegidos por rol

### Controladores
- `app/Controllers/Home.php` - Dashboard general (redirige según rol)
- `app/Controllers/Admin/DashboardController.php`
- `app/Controllers/Consultor/DashboardController.php`
- `app/Controllers/Proveedor/DashboardController.php`

### Vistas
- `app/Views/admin/dashboard.php`
- `app/Views/consultor/dashboard.php`
- `app/Views/proveedor/dashboard.php`

---

## Roles del Sistema

| ID  | Nombre       | Descripción                    |
|-----|--------------|--------------------------------|
| 1   | super_admin  | Administrador del sistema      |
| 2   | consultor    | Auditor/revisor                |
| 3   | proveedor    | Usuario proveedor              |

---

## Preparación de Datos de Prueba

### 1. Crear usuarios de prueba

Ejecutar los siguientes queries en MySQL para crear usuarios de prueba:

```sql
-- Usuario Consultor
INSERT INTO users (email, password_hash, nombre, id_roles, estado, created_at)
VALUES ('consultor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Consultor', 2, 'activo', NOW());
-- Contraseña: password

-- Usuario Proveedor
INSERT INTO users (email, password_hash, nombre, id_roles, estado, created_at)
VALUES ('proveedor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María Proveedor', 3, 'activo', NOW());
-- Contraseña: password
```

**Nota:** El hash corresponde a la contraseña `password`. Si prefieres otra contraseña, genera el hash con:

```php
<?php echo password_hash('tu_contraseña', PASSWORD_DEFAULT); ?>
```

---

## Casos de Prueba

### Caso 1: Usuario NO autenticado

**Objetivo:** Verificar que usuarios sin sesión son redirigidos al login.

**Pasos:**
1. Cerrar sesión si está abierta: http://localhost/auditorias/public/logout
2. Intentar acceder a: http://localhost/auditorias/public/admin/dashboard
3. **Resultado esperado:** Redirige a `/login` con mensaje "Debe iniciar sesión para acceder."

**Repetir con:**
- http://localhost/auditorias/public/consultor/dashboard
- http://localhost/auditorias/public/proveedor/dashboard
- http://localhost/auditorias/public/dashboard

---

### Caso 2: Usuario Super Admin (rol 1)

**Credenciales:**
- Email: `superadmin@cycloidtalent.com`
- Contraseña: `Admin123*`

**Pasos:**
1. Iniciar sesión en: http://localhost/auditorias/public/login
2. Acceder a: http://localhost/auditorias/public/dashboard
3. **Resultado esperado:** Redirige automáticamente a `/admin/dashboard`
4. Verificar que se muestra el panel de administración con navbar azul

**Pruebas adicionales:**
- ✅ Acceso permitido: http://localhost/auditorias/public/admin/dashboard
- ❌ Acceso denegado: http://localhost/auditorias/public/consultor/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"
- ❌ Acceso denegado: http://localhost/auditorias/public/proveedor/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"

---

### Caso 3: Usuario Consultor (rol 2)

**Credenciales:**
- Email: `consultor@test.com`
- Contraseña: `password`

**Pasos:**
1. Cerrar sesión del usuario anterior
2. Iniciar sesión con credenciales de consultor
3. Acceder a: http://localhost/auditorias/public/dashboard
4. **Resultado esperado:** Redirige automáticamente a `/consultor/dashboard`
5. Verificar que se muestra el panel de consultor con navbar verde

**Pruebas adicionales:**
- ❌ Acceso denegado: http://localhost/auditorias/public/admin/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"
- ✅ Acceso permitido: http://localhost/auditorias/public/consultor/dashboard
- ❌ Acceso denegado: http://localhost/auditorias/public/proveedor/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"

---

### Caso 4: Usuario Proveedor (rol 3)

**Credenciales:**
- Email: `proveedor@test.com`
- Contraseña: `password`

**Pasos:**
1. Cerrar sesión del usuario anterior
2. Iniciar sesión con credenciales de proveedor
3. Acceder a: http://localhost/auditorias/public/dashboard
4. **Resultado esperado:** Redirige automáticamente a `/proveedor/dashboard`
5. Verificar que se muestra el panel de proveedor con navbar amarilla

**Pruebas adicionales:**
- ❌ Acceso denegado: http://localhost/auditorias/public/admin/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"
- ❌ Acceso denegado: http://localhost/auditorias/public/consultor/dashboard
  - **Resultado esperado:** Redirige a `/login` con mensaje "Acceso denegado"
- ✅ Acceso permitido: http://localhost/auditorias/public/proveedor/dashboard

---

## Verificación de Funciones del Helper

Puedes probar las funciones del helper creando un controlador temporal o agregando esto en cualquier vista:

```php
<?php
helper('auth');

// Verificar funciones
var_dump([
    'isLogged' => isLogged(),
    'userId' => userId(),
    'userRole' => userRole(),
    'userEmail' => userEmail(),
    'userName' => userName(),
    'isSuperAdmin' => isSuperAdmin(),
    'isConsultor' => isConsultor(),
    'isProveedor' => isProveedor(),
    'currentRoleName' => currentRoleName(),
]);
?>
```

---

## Verificación de CSRF

El CSRF está habilitado globalmente. Todos los formularios deben incluir:

```php
<?= csrf_field() ?>
```

**Prueba:**
1. Inspeccionar el formulario de login
2. Verificar que existe un campo hidden con nombre `csrf_test_name`
3. Intentar enviar el formulario sin el token debería fallar

---

## Checklist de Verificación

- [ ] Usuario sin sesión no puede acceder a rutas protegidas
- [ ] Super Admin accede solo a `/admin/*`
- [ ] Consultor accede solo a `/consultor/*`
- [ ] Proveedor accede solo a `/proveedor/*`
- [ ] Dashboard general (`/dashboard`) redirige según el rol
- [ ] Mensajes flash se muestran correctamente
- [ ] CSRF está activo en todos los formularios
- [ ] Las funciones del helper funcionan correctamente
- [ ] Los tres dashboards tienen diseño diferenciado por color
- [ ] El logout funciona correctamente desde cualquier rol

---

## Troubleshooting

### Error: "Class App\Filters\AuthFilter not found"
**Solución:** Ejecutar `composer dump-autoload` para regenerar el autoloader.

### Error: "csrf_verify failed"
**Solución:** Verificar que el formulario incluye `<?= csrf_field() ?>`

### Sesión no persiste
**Solución:** Verificar configuración de sesión en `app/Config/Session.php`

### Redirecciones infinitas
**Solución:** Verificar que las rutas de login (`/login`) NO tienen filtros aplicados

---

## Próximos Pasos

El sistema de filtros está listo para:
1. Implementar el CRUD de `items_banco` (solo super_admin)
2. Implementar CRUDs de clientes, proveedores, consultores
3. Implementar módulo de auditorías
4. Implementar sistema de notificaciones

---

**Fecha de implementación:** 2025-10-14
**Framework:** CodeIgniter 4.6.x
**Autor:** Sistema de Auditorías - Cycloid Talent SAS
