#!/usr/bin/env bash
# Automates initial setup for a PHP/Apache app that expects:
# - Apache modules: headers, rewrite
# - Composer install
# - config/config.php copied from config/config.dist.php
#
# Usage:
#   ./install.sh --install-password 'secret' --web-user www-data --restart-apache
#
# Notes:
# - Safe to re-run (idempotent).
# - Designed for Debian/Ubuntu (uses a2enmod/systemctl). Adapt comments for other distros.

set -euo pipefail

### -------- CLI args --------
INSTALL_PASSWORD=""
WEB_USER="www-data"
RESTART_APACHE=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    --install-password)
      INSTALL_PASSWORD="${2:-}"; shift 2;;
    --web-user)
      WEB_USER="${2:-}"; shift 2;;
    --restart-apache)
      RESTART_APACHE=true; shift 1;;
    *)
      echo "Unknown arg: $1" >&2; exit 1;;
  esac
done

if [[ -z "$INSTALL_PASSWORD" ]]; then
  echo "ERROR: --install-password is required" >&2
  exit 2
fi

### -------- sanity checks --------
need() { command -v "$1" >/dev/null 2>&1 || { echo "Missing dependency: $1" >&2; exit 3; }; }

need php
need composer
need apachectl

if ! id -u "$WEB_USER" >/dev/null 2>&1; then
  echo "ERROR: web user '$WEB_USER' does not exist" >&2
  exit 4
fi

### -------- detect apache & enable modules --------
# Check if a module is loaded; if not, and a2enmod exists, enable it.
has_mod() { apachectl -M 2>/dev/null | grep -qE "^ ${1}_module"; }

enable_mod_if_needed() {
  local mod="$1"
  if has_mod "$mod"; then
    echo "Apache module '$mod' already enabled."
  else
    if command -v a2enmod >/dev/null 2>&1; then
      echo "Enabling Apache module '$mod'..."
      sudo a2enmod "$mod"
      # we’ll reload later if --restart-apache was set
    else
      echo "WARN: a2enmod not found; enable module '$mod' manually." >&2
    fi
  fi
}

enable_mod_if_needed "headers"
enable_mod_if_needed "rewrite"

### -------- composer install --------
# - Production-friendly flags
# - No scripts: keep default (some projects rely on scripts); flip to --no-scripts if needed
echo "Running composer install..."
composer install --no-interaction --prefer-dist --no-progress

### -------- config provisioning --------
# Copy config/config.dist.php -> config/config.php if needed
if [[ -f "./config/config.php" ]]; then
  echo "config/config.php already exists; not overwriting."
elif [[ -f "./config/config.dist.php" ]]; then
  echo "Creating config/config.php from config/config.dist.php..."
  cp ./config/config.dist.php ./config/config.php
else
  echo "ERROR: config/config.dist.php not found." >&2
  exit 5
fi

# Set the install.password in config.php if a recognizable key exists.
# This targets array-style PHP config like: 'install.password' => ''
if grep -q "'install\.password'" ./config/config.php; then
  echo "Setting install.password in config/config.php..."
  # Escape slashes and quotes for sed
  esc_pw=$(printf '%s' "$INSTALL_PASSWORD" | sed -e 's/[\/&]/\\&/g')
  # Replace the value part between quotes after the key 'install.password'
  sed -i -E "s/('install\.password'\s*=>\s*)'[^']*'/\1'$esc_pw'/" ./config/config.php
else
  echo "WARN: 'install.password' key not found in config/config.php; please set it manually."
fi

### -------- recommended PHP extensions check (non-fatal) --------
# Adjust this list to match the app’s requirements.
REQUIRED_EXTS=(pdo pdo_mysql mbstring intl curl gd xml json)
MISSING=()
for ext in "${REQUIRED_EXTS[@]}"; do
  if ! php -m | awk '{print tolower($0)}' | grep -q "^$ext$"; then
    MISSING+=("$ext")
  fi
done
if ((${#MISSING[@]})); then
  echo "WARN: missing PHP extensions: ${MISSING[*]}"
  echo "      Install them (e.g., sudo apt-get install php-mysql php-mbstring php-intl php-curl php-gd php-xml)."
fi

### -------- writable dirs (adjust as your app needs) --------
# Typical writable paths; add/remove to fit the project.
WRITABLE_DIRS=(
  "./uploads"
  "./uploads/images"
  "./uploads/attachments"
  "./cache"
  "./tpl_c"
  "./templates_c"
  "./storage"
  "./logs"
)

for d in "${WRITABLE_DIRS[@]}"; do
  if [[ -d "$d" ]]; then
    echo "Ensuring write permission for $WEB_USER on $d"
    sudo chown -R "$WEB_USER":"$WEB_USER" "$d"
    sudo find "$d" -type d -exec chmod 775 {} \;
    sudo find "$d" -type f -exec chmod 664 {} \;
  fi
done

### -------- Apache reload (optional) --------
if $RESTART_APACHE; then
  if command -v systemctl >/dev/null 2>&1; then
    echo "Reloading Apache..."
    sudo systemctl reload apache2 || sudo systemctl restart apache2
  else
    echo "WARN: systemctl not found; reload Apache manually." >&2
  fi
else
  echo "Skipping Apache reload (pass --restart-apache to enable)."
fi

echo "✅ Install completed."
