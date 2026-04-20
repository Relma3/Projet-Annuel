# Docker

## Services

Le projet utilise 5 services :

- web : le site en PHP
- go-api : l'api en Go
- db : la base de données
- phpmyadmin : pour voir la base plus facilement
- dockge : pour gérer docker en interface

## Fonctionnement

Le service web communique avec l'api Go et avec la base de données.
phpmyadmin sert seulement en développement.
dockge sert aussi seulement en développement.

## Lancer le projet

cp .env.example .env
docker compose -f docker-compose.dev.yml up --build

## Adresses utiles

http://localhost
http://localhost:8080
http://localhost:8081
http://localhost:5001

## Commandes utiles

docker compose -f docker-compose.dev.yml exec web php database/seeder.php
docker compose -f docker-compose.dev.yml logs -f
docker compose -f docker-compose.dev.yml down

## Production

docker build -t ghcr.io/Mmina0401/silverhappy-web:latest .
docker build -t ghcr.io/Mmina0401/silverhappy-goapi:latest ./go-api
docker push ghcr.io/Mmina0401/silverhappy-web:latest
docker push ghcr.io/Mmina0401/silverhappy-goapi:latest
docker compose -f docker-compose.prod.yml up -d

## Fichiers

Dockerfile
docker-compose.dev.yml
docker-compose.prod.yml
.env.example
go-api/Dockerfile