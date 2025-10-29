# 🚀 Inicio Rápido - Admin Module

## Para usuarios de Windows (XAMPP)

### Opción más fácil (1 clic):

**Haz doble clic en:**
```
📄 run_admin_setup.bat
```

### Opción manual (2 comandos):

1. Abrir terminal en la carpeta del proyecto
2. Ejecutar:

```cmd
php spark db:seed AdminQuickSeed
php test_admin_workflow.php
```

---

## Para usuarios de Linux/Mac

### Opción más fácil (1 comando):

```bash
chmod +x run_admin_setup.sh && ./run_admin_setup.sh
```

### Opción manual (2 comandos):

```bash
php spark db:seed AdminQuickSeed
php test_admin_workflow.php
```

---

## 🔑 Acceso al Sistema

Después de ejecutar el setup:

**URL:** http://localhost/auditorias/login

**Credenciales Admin:**
- Email: `superadmin@cycloidtalent.com`
- Contraseña: `Admin123*`

---

## 📊 ¿Qué se crea?

✅ **3 Clientes** de prueba
✅ **2 Proveedores** de prueba
✅ **2 Consultores** con licencias
✅ **2 Contratos** activos
✅ **1 Servicio** (Auditoría SST)
✅ **4 Usuarios** (admin + consultores + proveedor)

---

## 📚 Más Información

Lee el archivo completo: [README_ADMIN_QUICKSEED.md](README_ADMIN_QUICKSEED.md)

---

**¿Problemas?** Revisa que:
- ✅ XAMPP/MySQL esté corriendo
- ✅ Base de datos esté configurada en `app/Config/Database.php`
- ✅ Migraciones ejecutadas: `php spark migrate`
