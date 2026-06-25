#!/usr/bin/env bash
# Synchronisation du registre RPPS (Annuaire Santé ANS) dans MariaDB.
# Télécharge l'extraction publique (MAJ quotidienne), la transforme et la (re)charge
# dans la table ps_hfm_rpps_registry via une bascule atomique (aucune coupure de service).
#
# Utilisé manuellement ou par cron (tous les jours à minuit). Log dans var/logs/rpps_sync.log
set -euo pipefail

# PATH complet (cron a un PATH minimal -> docker/curl/awk introuvables sinon)
export PATH="/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin:/usr/sbin:/sbin:$PATH"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WORK="$(mktemp -d /tmp/rpps_sync.XXXXXX)"
DB_CONTAINER="hyaluronic-db"
DB_USER="root"; DB_PASS="root"; DB_NAME="admin_"
PREFIX="ps_"
URL="https://www.data.gouv.fr/api/1/datasets/r/fffda7e9-0ea2-4c35-bba0-4496f3af935d"
LOG="$ROOT/var/logs/rpps_sync.log"
mkdir -p "$ROOT/var/logs"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG"; }
cleanup() { rm -rf "$WORK"; }
trap cleanup EXIT

log "=== Sync RPPS démarrée ==="

log "Téléchargement de l'extraction publique…"
curl -sSL -m 900 -o "$WORK/ps.raw" "$URL"
SIZE=$(wc -c < "$WORK/ps.raw")
if [ "$SIZE" -lt 50000000 ]; then
  log "ERREUR : fichier trop petit ($SIZE octets), abandon (registre conservé)."
  exit 1
fi
log "Téléchargé : $(du -h "$WORK/ps.raw" | cut -f1)"

log "Transformation (col2=RPPS, col8=nom, col9=prénom, col11=profession ; dédup)…"
awk -F'|' 'NR>1 && $2!="" && !seen[$2]++ {gsub(/\t/,"",$8);gsub(/\t/,"",$9);gsub(/\t/,"",$11); print $2"\t"$8"\t"$9"\t"$11}' "$WORK/ps.raw" > "$WORK/rpps.tsv"
N=$(wc -l < "$WORK/rpps.tsv")
log "RPPS uniques : $N"
if [ "$N" -lt 1000000 ]; then
  log "ERREUR : trop peu de lignes ($N), abandon (registre conservé)."
  exit 1
fi

log "Chargement en base (table _new)…"
docker exec "$DB_CONTAINER" mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
DROP TABLE IF EXISTS ${PREFIX}hfm_rpps_registry_new;
CREATE TABLE ${PREFIX}hfm_rpps_registry_new (
  rpps VARCHAR(11) NOT NULL PRIMARY KEY,
  nom VARCHAR(160), prenom VARCHAR(160), profession VARCHAR(160)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" 2>/dev/null

docker cp "$WORK/rpps.tsv" "$DB_CONTAINER:/tmp/rpps_sync.tsv"
docker exec "$DB_CONTAINER" mariadb --local-infile=1 -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SET GLOBAL local_infile=1;
LOAD DATA LOCAL INFILE '/tmp/rpps_sync.tsv' INTO TABLE ${PREFIX}hfm_rpps_registry_new
FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n' (rpps,nom,prenom,profession);" 2>/dev/null
docker exec "$DB_CONTAINER" rm -f /tmp/rpps_sync.tsv 2>/dev/null || true

LOADED=$(docker exec "$DB_CONTAINER" mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM ${PREFIX}hfm_rpps_registry_new;" 2>/dev/null)
log "Chargé en base : $LOADED"
if [ "${LOADED:-0}" -lt 1000000 ]; then
  log "ERREUR : chargement incomplet ($LOADED), bascule annulée."
  exit 1
fi

log "Bascule atomique…"
docker exec "$DB_CONTAINER" mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
DROP TABLE IF EXISTS ${PREFIX}hfm_rpps_registry_old;
RENAME TABLE ${PREFIX}hfm_rpps_registry TO ${PREFIX}hfm_rpps_registry_old, ${PREFIX}hfm_rpps_registry_new TO ${PREFIX}hfm_rpps_registry;
DROP TABLE IF EXISTS ${PREFIX}hfm_rpps_registry_old;" 2>/dev/null

log "=== Sync RPPS terminée : $LOADED praticiens ==="
