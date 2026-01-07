#!/usr/bin/env bash
set -euo pipefail

# Backup DB (plain .sql) and optionally send to Telegram.

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

# Load DB creds from .env (tolerates spaces/quotes)
ENV_FILE="$PROJECT_ROOT/.env"
if [[ ! -f "$ENV_FILE" ]]; then
  echo ".env not found at $PROJECT_ROOT" >&2
  exit 1
fi

DB_HOST=""
DB_PORT=""
DB_NAME=""
DB_USER=""
DB_PASS=""
APP_ENV=""
TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-}"
TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:-}"
ALLOW_LOCAL_BACKUP="${ALLOW_LOCAL_BACKUP:-}"

while IFS='=' read -r key value; do
  value="${value%%#*}"             # strip inline comments
  value="${value%$'\r'}"           # trim CR
  value="${value//\"/}"            # strip double quotes
  value="${value//\'/}"            # strip single quotes
  case "$key" in
    DB_HOST) DB_HOST="${value}";;
    DB_PORT) DB_PORT="${value}";;
    DB_DATABASE) DB_NAME="${value}";;
    DB_USERNAME) DB_USER="${value}";;
    DB_PASSWORD) DB_PASS="${value}";;
    APP_ENV) APP_ENV="${value}";;
    TELEGRAM_BOT_TOKEN) TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-$value}";;
    TELEGRAM_CHAT_ID) TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:-$value}";;
    ALLOW_LOCAL_BACKUP) ALLOW_LOCAL_BACKUP="${ALLOW_LOCAL_BACKUP:-$value}";;
  esac
done < <(grep -E '^[[:space:]]*(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD|APP_ENV|TELEGRAM_BOT_TOKEN|TELEGRAM_CHAT_ID|ALLOW_LOCAL_BACKUP)[[:space:]]*=' "$ENV_FILE" \
        | sed -E 's/^[[:space:]]*//' \
        | sed -E 's/[[:space:]]*=[[:space:]]*/=/')

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:?DB_DATABASE missing in .env}"
DB_USER="${DB_USER:?DB_USERNAME missing in .env}"
DB_PASS="${DB_PASS:-}"
APP_ENV="${APP_ENV:-}"

lower() { printf '%s' "$1" | tr '[:upper:]' '[:lower:]'; }
if [[ "$(lower "${APP_ENV:-}")" == "local" && "$(lower "${ALLOW_LOCAL_BACKUP:-}")" != "true" ]]; then
  echo "APP_ENV=local; set ALLOW_LOCAL_BACKUP=true to enable."
  exit 0
fi

BACKUP_DIR="$PROJECT_ROOT/DBBACKUP"
mkdir -p "$BACKUP_DIR"

export TZ=Asia/Kathmandu
STAMP="$(date +'%Y%m%d-%H%M%S')"
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}-${STAMP}.sql"

MYSQLDUMP_BIN="${MYSQLDUMP_BIN:-mysqldump}"

if [[ ! -x "$MYSQLDUMP_BIN" ]]; then
  if command -v "$MYSQLDUMP_BIN" >/dev/null 2>&1; then
    MYSQLDUMP_BIN="$(command -v "$MYSQLDUMP_BIN")"
  else
    for candidate in \
      /Applications/XAMPP/bin/mysqldump \
      /usr/local/mysql/bin/mysqldump \
      /usr/local/bin/mysqldump \
      /opt/homebrew/bin/mysqldump; do
      if [[ -x "$candidate" ]]; then
        MYSQLDUMP_BIN="$candidate"
        break
      fi
    done
  fi
fi

if [[ ! -x "$MYSQLDUMP_BIN" ]]; then
  echo "mysqldump not found. Set MYSQLDUMP_BIN=/path/to/mysqldump (e.g., /Applications/XAMPP/bin/mysqldump)." >&2
  exit 1
fi

echo "Creating backup: $BACKUP_FILE"
"$MYSQLDUMP_BIN" --single-transaction --quick --lock-tables=false \
  -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" --password="$DB_PASS" \
  "$DB_NAME" > "$BACKUP_FILE"

if [[ -n "${TELEGRAM_BOT_TOKEN:-}" && -n "${TELEGRAM_CHAT_ID:-}" ]]; then
  echo "Sending backup to Telegram chat $TELEGRAM_CHAT_ID"
  curl -sSf -F document=@"$BACKUP_FILE" \
    -F chat_id="$TELEGRAM_CHAT_ID" \
    "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendDocument" >/dev/null
else
  echo "TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID not set; skipping Telegram send."
fi

echo "Done."

# Retention: keep last ~6 months (180 days)
find "$BACKUP_DIR" -type f -name "${DB_NAME}-*.sql" -mtime +180 -print -delete || true
