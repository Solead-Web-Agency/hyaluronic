#!/usr/bin/env bash
# Démarrage de la boutique en local (PrestaShop 9.1.4)
# - Base de données : MariaDB 10.6 dans Docker (docker-compose.yml)
# - Serveur web     : PHP 8.1 intégré
set -e

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

PHP_BIN="${PHP_BIN:-php}"            # PHP 8.1+ requis pour PrestaShop 9
PORT="${PORT:-8080}"
ADMIN_DIR="admin78026"

echo "==> Démarrage de la base (Docker MariaDB)…"
docker compose up -d

echo "==> Attente de la disponibilité de MariaDB…"
for i in $(seq 1 30); do
  if docker exec hyaluronic-db mariadb -uroot -proot -e "SELECT 1" >/dev/null 2>&1; then
    echo "    base prête."
    break
  fi
  sleep 2
done

echo "==> Purge du cache PrestaShop…"
rm -rf var/cache/* 2>/dev/null || true

# Libère le port si déjà utilisé
lsof -nP -iTCP:"$PORT" -sTCP:LISTEN -t 2>/dev/null | xargs kill 2>/dev/null || true

echo "==> Boutique :        http://localhost:$PORT/"
echo "==> Back-office :     http://localhost:$PORT/$ADMIN_DIR/"
echo "==> (Ctrl+C pour arrêter le serveur web ; la base reste up — 'docker compose stop' pour l'arrêter)"
# PHP_CLI_SERVER_WORKERS : indispensable pour le back-office (requêtes AJAX concurrentes),
# sinon le serveur mono-processus se bloque -> ERR_EMPTY_RESPONSE / boucles de jeton.
PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-8}" exec "$PHP_BIN" -d memory_limit=4G -d max_execution_time=0 \
  -d upload_max_filesize=128M -d post_max_size=128M \
  -S 0.0.0.0:"$PORT" -t "$ROOT"
