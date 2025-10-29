# Flujo de Creación de Usuarios

## Resumen

El sistema ahora permite **crear usuarios con contraseña directamente** al crear Consultores o Proveedores, eliminando la necesidad de crear el usuario primero en un paso separado.

---

## 📋 Módulos Actualizados

### 1. **Consultores**
- **Ruta:** `/admin/consultores/crear`
- **Rol de usuario:** Consultor (id_roles = 2)
- **Campos de acceso:**
  - Email (requerido)
  - Contraseña (requerido, mínimo 8 caracteres)

### 2. **Proveedores**
- **Ruta:** `/admin/proveedores/crear`
- **Rol de usuario:** Proveedor (id_roles = 3)
- **Campos de acceso:**
  - Email (opcional)
  - Contraseña (opcional, mínimo 8 caracteres)

---

## 🔄 Flujos Disponibles

### Opción A: Crear Usuario Nuevo (Recomendado)
**Al crear Consultor o Proveedor:**

1. Ve al formulario de creación (Consultores o Proveedores)
2. Llena los datos del Consultor/Proveedor
3. **Llena los campos de "Datos de Acceso":**
   - Email: Email para login
   - Contraseña: La contraseña que TÚ defines
4. Guarda
5. ✅ El usuario puede iniciar sesión inmediatamente

**Ventajas:**
- Todo en un solo paso
- TÚ defines la contraseña
- Más intuitivo

### Opción B: Usar Usuario Existente
**Flujo antiguo (aún funciona):**

1. Primero crear el usuario en `/admin/usuarios`
2. Copiar la contraseña temporal generada
3. Luego crear el Consultor/Proveedor
4. Seleccionar el usuario creado

**Cuándo usar:**
- Cuando ya tienes un usuario creado
- Para migrar usuarios existentes

---

## 🔑 Gestión de Contraseñas

### Resetear Contraseña
Si un usuario olvida su contraseña:

1. Ve a `/admin/usuarios`
2. Busca el usuario
3. Haz clic en el botón **🔑** (llave)
4. Confirma la acción
5. **Copia la nueva contraseña temporal** que aparece en el mensaje verde
6. Comparte la contraseña con el usuario

### Al Editar Consultor/Proveedor
- **No se puede cambiar la contraseña** desde el formulario de edición
- El campo de usuario aparece deshabilitado
- Para cambiar contraseña, usa el módulo de Usuarios

---

## 📊 Roles de Usuario

| Rol          | ID | Descripción                                    |
|--------------|----|------------------------------------------------|
| Super Admin  | 1  | Administrador total del sistema                |
| Consultor    | 2  | Crea y gestiona auditorías                     |
| Proveedor    | 3  | Completa auditorías asignadas                  |

---

## ✅ Ventajas del Nuevo Flujo

1. **Simplificado**: Un solo formulario en lugar de dos pasos
2. **Control total**: El admin define la contraseña desde el inicio
3. **Sin contraseñas perdidas**: No más contraseñas temporales aleatorias
4. **Intuitivo**: Todo centralizado en un solo lugar
5. **Flexible**: Soporta ambos flujos (nuevo y antiguo)

---

## 🔧 Implementación Técnica

### Controladores Modificados
- `ConsultoresController::store()` - Líneas 158-201
- `ProveedoresController::store()` - Líneas 105-166

### Vistas Modificadas
- `admin/consultores/form.php` - Líneas 70-119
- `admin/proveedores/form.php` - Líneas 139-190

### Lógica de Creación

```php
// 1. Si se proporciona email y password, crear usuario nuevo
if ($email && $password) {
    $userData = [
        'nombre'        => $nombreCompleto,
        'email'         => strtolower(trim($email)),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'id_roles'      => 2, // o 3 para proveedor
        'estado'        => 'activo',
    ];

    $userModel->save($userData);
    $idUser = $userModel->getInsertID();
}

// 2. Crear el consultor/proveedor con el id_user
$data = [
    'id_users' => $idUser,
    // ... otros campos
];
```

---

## 📝 Notas Importantes

- La contraseña se hashea con `PASSWORD_DEFAULT` (bcrypt)
- Los emails se convierten automáticamente a minúsculas
- La validación de email único se hace en el modelo de Users
- Los usuarios creados automáticamente quedan en estado 'activo'

---

Última actualización: 2025-10-20
