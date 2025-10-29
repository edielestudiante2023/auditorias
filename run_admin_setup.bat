@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   Setup Rápido Admin - Auditorías
echo ========================================
echo.

REM Verificar que estamos en el directorio correcto
if not exist "spark" (
    echo [ERROR] No se encuentra el archivo 'spark'
    echo Por favor ejecuta este script desde la raíz del proyecto.
    pause
    exit /b 1
)

REM Paso 1: Ejecutar el seeder
echo [1/2] Ejecutando seeder AdminQuickSeed...
echo.
php spark db:seed AdminQuickSeed

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] El seeder falló. Verifica tu configuración de base de datos.
    pause
    exit /b 1
)

echo.
echo ========================================
echo   Seeder completado exitosamente
echo ========================================
echo.

REM Preguntar si ejecutar el test
echo ¿Deseas ejecutar el script de prueba? (S/N)
set /p ejecutar_test="> "

if /i "%ejecutar_test%"=="S" (
    echo.
    echo [2/2] Ejecutando script de prueba...
    echo.
    php test_admin_workflow.php

    if %errorlevel% neq 0 (
        echo.
        echo [ADVERTENCIA] El script de prueba tuvo algunos errores.
        echo Revisa los mensajes arriba para más detalles.
    ) else (
        echo.
        echo ========================================
        echo   Pruebas completadas exitosamente
        echo ========================================
    )
) else (
    echo.
    echo [INFO] Pruebas omitidas. Puedes ejecutarlas manualmente con:
    echo   php test_admin_workflow.php
)

echo.
echo ========================================
echo   Setup completado
echo ========================================
echo.
echo Credenciales de acceso:
echo   URL:    http://localhost/auditorias/login
echo   Admin:  superadmin@cycloidtalent.com
echo   Pass:   Admin123*
echo.
echo Presiona cualquier tecla para salir...
pause > nul
