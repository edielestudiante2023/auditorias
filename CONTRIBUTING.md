# Guia de Contribucion — Auditorias SG-SST

## Flujo de ramas

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Cambios se unen aqui antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Crear una feature

```bash
git checkout develop
git pull origin develop
git checkout -b feature/modulo-descripcion
# ... trabajar ...
git push origin feature/modulo-descripcion
# Crear PR hacia develop
```

### Crear un hotfix

```bash
git checkout main
git pull origin main
git checkout -b hotfix/bug-descripcion
# ... corregir ...
git push origin hotfix/bug-descripcion
# Crear PR hacia main Y hacia develop
```

## Convencion de commits

Usar prefijos descriptivos:

| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Correccion de bug |
| `docs:` | Cambios en documentacion |
| `refactor:` | Refactorizacion sin cambio funcional |
| `chore:` | Tareas de mantenimiento (deps, config) |
| `test:` | Agregar o modificar tests |
| `style:` | Cambios de formato (no afectan logica) |

Ejemplos:
```
feat: agregar filtro por estado en auditorias del consultor
fix: corregir calculo de porcentaje de cumplimiento
docs: actualizar guia de deploy
refactor: extraer logica de email a EmailService
```

## Convencion de nombres de ramas

```
feature/admin-dashboard-reportes
feature/consultor-pdf-mejoras
feature/proveedor-evidencias-multiple
hotfix/fix-login-redirect
hotfix/fix-pdf-encoding
```

## Reglas

1. **No push directo a `main`** — siempre via PR desde develop
2. **No push directo a `develop`** — siempre via PR desde feature/
3. **No credenciales en el codigo** — usar .env para API keys, passwords, tokens
4. **No archivos temporales** — no commitear debug_*.php, test_*.php, fix_*.php, *.bak
5. **No operaciones destructivas en produccion** — no DELETE sin WHERE, no DROP sin respaldo

## Proceso de revision

1. Desarrollador crea PR desde `feature/` hacia `develop`
2. Pipeline CI/CD valida automaticamente:
   - Sintaxis PHP (`php -l`)
   - Escaneo de vulnerabilidades (Trivy)
   - Analisis estatico de seguridad (Semgrep)
   - Busqueda de credenciales hardcodeadas
3. Revisor aprueba o solicita cambios
4. Merge a `develop` -> deploy automatico a QA
5. PR de `develop` a `main` -> deploy automatico a produccion

## Checklist antes de hacer PR

- [ ] El codigo compila sin errores (`php -l`)
- [ ] No hay credenciales hardcodeadas
- [ ] No hay archivos temporales o de debug
- [ ] Los cambios estan probados localmente
- [ ] El mensaje de commit sigue la convencion
