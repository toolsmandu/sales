#!/usr/bin/env bash
set -euo pipefail

# Wrapper for cron to keep the command short. Fill in your live token/chat ID below.

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Set your Telegram values here (override with env vars if you prefer).
TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-PASTE_YOUR_LIVE_TOKEN}"
TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:--123456789}"

# Path to mysqldump on Hostinger (adjust if different).
export MYSQLDUMP_BIN="${MYSQLDUMP_BIN:-/usr/bin/mysqldump}"
export TELEGRAM_BOT_TOKEN TELEGRAM_CHAT_ID

/bin/bash "$PROJECT_ROOT/db_backup.sh"
