#!/usr/bin/env bash
set -euo pipefail

# Wrapper for cron to keep the command short. Fill in your live token/chat ID below.

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Set your Telegram values here (override with env vars if you prefer).
TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-8599217403:AAHn11yga4i4KQBzo6cOdyq_Jg4wZAnJlbw}"
TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:--5009379401}"

# Path to mysqldump on Hostinger (adjust if different).
export MYSQLDUMP_BIN="${MYSQLDUMP_BIN:-home/u862833879/backups}"
export TELEGRAM_BOT_TOKEN TELEGRAM_CHAT_ID

/bin/bash "$PROJECT_ROOT/db_backup.sh"
