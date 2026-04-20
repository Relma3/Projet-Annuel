#!/bin/bash

set -e

echo "Verification des outils"

command -v docker >/dev/null 2>&1 || { echo "Docker non installe"; exit 1; }
command -v git >/dev/null 2>&1 || { echo "Git non installe"; exit 1; }

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo ".env cree"
        echo "Pense a remplir le fichier .env"
        read -p "Appuie sur Entree quand c'est bon"
    else
        echo ".env.example introuvable"
        exit 1
    fi
fi

mkdir -p storage/factures storage/logs
chmod 755 storage storage/factures storage/logs

echo "Installation Composer"
if [ ! -f vendor/autoload.php ]; then
    if command -v composer >/dev/null 2>&1; then
        composer install
    else
        docker run --rm -v "$(pwd)":/app composer:2 install
    fi
fi

echo "Demarrage Docker"
docker compose down 2>/dev/null || true
docker compose build
docker compose up -d

echo "Attente de la base"
sleep 10

DB_NAME=$(grep '^DB_NAME=' .env | cut -d= -f2)
DB_USER=$(grep '^DB_USER=' .env | cut -d= -f2)
DB_PASS=$(grep '^DB_PASS=' .env | cut -d= -f2)

if [ -f database/schema.sql ]; then
    docker compose exec -T db mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
    echo "Schema importe"
fi

if [ -f database/schema_additions.sql ]; then
    docker compose exec -T db mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema_additions.sql || true
    echo "Schema additions importe"
fi

read -p "Lancer le seeder ? [o/N] : " SEED
if [ "$SEED" = "o" ] || [ "$SEED" = "O" ]; then
    docker compose exec -T web php database/seeder.php
    echo "Seeder termine"
fi

echo ""
echo "Installation terminee"
echo "Site : http://localhost"
echo "API Go : http://localhost:8080"
echo "phpMyAdmin : http://localhost:8081"