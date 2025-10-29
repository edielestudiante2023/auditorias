@echo off
chcp 65001 > nul
echo.
echo ========================================
echo   Pre-Flight Check - Auditorías
echo ========================================
echo.

php pre_flight_check.php

if %errorlevel% equ 0 (
    echo.
    echo ¿Deseas ejecutar el seeder ahora? (S/N)
    set /p ejecutar_seeder="> "

    if /i "%ejecutar_seeder%"=="S" (
        echo.
        echo Ejecutando seeder...
        php spark db:seed AdminQuickSeed
    )
) else (
    echo.
    echo [ERROR] Corrige los errores antes de continuar.
)

echo.
pause
