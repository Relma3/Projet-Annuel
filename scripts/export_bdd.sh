#!/bin/bash

source .env 2>/dev/null || true

DB_USER=${DB_USER:-sh_user}
DB_PASS=${DB_PASS:-}
DB_NAME=${DB_NAME:-silver_happy}

echo "Export de la base"

docker compose exec -T db mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} --no-data > database/bdd_vide.sql
echo "bdd_vide.sql cree"

docker compose exec -T web php database/seeder.php

docker compose exec -T db mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} > database/bdd_remplie.sql
echo "bdd_remplie.sql cree"

echo "Termine"