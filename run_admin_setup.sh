#!/bin/bash

# Script de setup para Linux/Mac
# Uso: ./run_admin_setup.sh

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Setup Rápido Admin - Auditorías${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "spark" ]; then
    echo -e "${RED}[ERROR] No se encuentra el archivo 'spark'${NC}"
    echo "Por favor ejecuta este script desde la raíz del proyecto."
    exit 1
fi

# Paso 1: Ejecutar el seeder
echo -e "${YELLOW}[1/2] Ejecutando seeder AdminQuickSeed...${NC}"
echo ""
php spark db:seed AdminQuickSeed

if [ $? -ne 0 ]; then
    echo ""
    echo -e "${RED}[ERROR] El seeder falló. Verifica tu configuración de base de datos.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Seeder completado exitosamente${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Preguntar si ejecutar el test
read -p "¿Deseas ejecutar el script de prueba? (s/n): " ejecutar_test

if [[ "$ejecutar_test" =~ ^[Ss]$ ]]; then
    echo ""
    echo -e "${YELLOW}[2/2] Ejecutando script de prueba...${NC}"
    echo ""
    php test_admin_workflow.php

    if [ $? -ne 0 ]; then
        echo ""
        echo -e "${YELLOW}[ADVERTENCIA] El script de prueba tuvo algunos errores.${NC}"
        echo "Revisa los mensajes arriba para más detalles."
    else
        echo ""
        echo -e "${GREEN}========================================${NC}"
        echo -e "${GREEN}  Pruebas completadas exitosamente${NC}"
        echo -e "${GREEN}========================================${NC}"
    fi
else
    echo ""
    echo -e "${BLUE}[INFO] Pruebas omitidas. Puedes ejecutarlas manualmente con:${NC}"
    echo "  php test_admin_workflow.php"
fi

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Setup completado${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo "Credenciales de acceso:"
echo "  URL:    http://localhost/auditorias/login"
echo "  Admin:  superadmin@cycloidtalent.com"
echo "  Pass:   Admin123*"
echo ""
