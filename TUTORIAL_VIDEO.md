# 🎥 Configuración del Tutorial en Video

## Ubicación de la URL del Tutorial

La URL del tutorial en video que se muestra en el email de invitación a proveedores está configurada en el archivo `.env`.

### Configuración Actual

```bash
# En el archivo .env
tutorial.videoUrl = 'https://www.youtube.com/'
```

## 📝 Cómo Actualizar la URL del Tutorial

### 1. Sube tu video a YouTube

1. Graba el tutorial mostrando cómo:
   - Iniciar sesión en el sistema
   - Navegar por el wizard de auditoría
   - Completar ítems globales
   - Completar ítems por cliente
   - Subir evidencias en PDF
   - Finalizar y enviar la auditoría

2. Sube el video a YouTube en tu canal de Cycloid Talent

3. Copia la URL del video (ejemplo: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`)

### 2. Actualiza el archivo .env

**En desarrollo (local):**
```bash
# Edita: c:\xampp\htdocs\auditorias\.env
tutorial.videoUrl = 'https://www.youtube.com/watch?v=TU_VIDEO_ID'
```

**En producción:**
```bash
# Edita: /var/www/auditorias/.env (en el servidor)
tutorial.videoUrl = 'https://www.youtube.com/watch?v=TU_VIDEO_ID'
```

### 3. Los cambios son inmediatos

No necesitas reiniciar el servidor. El próximo email que se envíe usará la nueva URL.

## 🎬 Recomendaciones para el Video Tutorial

### Duración sugerida: 5-10 minutos

### Contenido del Tutorial:

1. **Introducción (30 seg)**
   - Bienvenida al sistema de auditorías Cycloid Talent
   - Objetivo del tutorial

2. **Inicio de Sesión (1 min)**
   - Dónde encontrar las credenciales en el email
   - Cómo acceder al sistema
   - Recomendación de cambiar la contraseña

3. **Vista General del Wizard (1 min)**
   - Explicar la barra de progreso
   - Diferencia entre ítems globales y por cliente
   - Navegación entre ítems

4. **Completar un Ítem Global (2 min)**
   - Leer el título y descripción
   - Cómo preparar el PDF (mostrar herramientas iLovePDF)
   - Subir evidencia
   - Agregar comentarios (opcional)
   - Guardar y continuar

5. **Completar un Ítem Por Cliente (2 min)**
   - Seleccionar el cliente del dropdown
   - Explicar que debe completarse para cada cliente
   - Subir evidencia específica del cliente
   - Guardar

6. **Finalizar la Auditoría (1 min)**
   - Verificar que todos los ítems estén al 100%
   - Botón "Finalizar y Enviar a Revisión"
   - Qué sucede después del envío

7. **Consejos Finales (1 min)**
   - Preparar documentos antes de empezar
   - Usar las herramientas de PDF recomendadas
   - Contactar a Cycloid si tienen dudas

### Tips de Grabación:

✅ Usa un ambiente de prueba/demo
✅ Graba en resolución 1080p
✅ Usa audio claro (micrófono de calidad)
✅ Muestra el cursor claramente
✅ Habla pausado y claro
✅ Usa subtítulos si es posible
✅ Agrega música de fondo suave (opcional)
✅ Incluye intro/outro con logo de Cycloid Talent

### Herramientas de Grabación Recomendadas:

- **OBS Studio** (Gratuito) - https://obsproject.com/
- **Loom** (Gratuito para videos de hasta 5 min) - https://www.loom.com/
- **Camtasia** (De pago) - https://www.techsmith.com/video-editor.html
- **ShareX** (Gratuito, solo Windows) - https://getsharex.com/

## 📧 Vista Previa del Email

El tutorial aparece en el email de invitación con:

- 🎥 Título: "Tutorial en Video"
- Descripción clara del contenido
- Botón rojo de YouTube para hacer clic
- Se abre en una nueva pestaña

## 🔄 Actualización en Múltiples Ambientes

Si tienes múltiples ambientes (desarrollo, staging, producción), recuerda actualizar el `.env` en cada uno:

```bash
# Desarrollo
tutorial.videoUrl = 'https://www.youtube.com/watch?v=TU_VIDEO_ID'

# Staging (si existe)
tutorial.videoUrl = 'https://www.youtube.com/watch?v=TU_VIDEO_ID'

# Producción
tutorial.videoUrl = 'https://www.youtube.com/watch?v=TU_VIDEO_ID'
```

## ✅ Verificación

Después de actualizar la URL, puedes probar enviando un email de prueba:

1. Ve a: `http://localhost/auditorias/test-email`
2. Haz clic en "Test Invite Proveedor"
3. Verifica que el botón del tutorial tenga la URL correcta

---

**Fecha de creación:** 2025-10-30
**Última actualización:** 2025-10-30
