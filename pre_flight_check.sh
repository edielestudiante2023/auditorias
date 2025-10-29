#!/bin/bash

echo ""
echo "========================================"
echo "  Pre-Flight Check - Auditorías"
echo "========================================"
echo ""

php pre_flight_check.php

if [ $? -eq 0 ]; then
    echo ""
    read -p "¿Deseas ejecutar el seeder ahora? (s/n): " ejecutar_seeder

    if [[ "$ejecutar_seeder" =~ ^[Ss]$ ]]; then
        echo ""
        echo "Ejecutando seeder..."
        php spark db:seed AdminQuickSeed
    fi
else
    echo ""
    echo "[ERROR] Corrige los errores antes de continuar."
fi

echo ""
