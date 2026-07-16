#!/usr/bin/env bash
# Guided local installer for NXTM on Ubuntu/WSL.
# Run this from inside a clone of the repo, or standalone — it will clone
# itself if the code isn't already present. Safe to re-run: every step
# checks current state first and skips what's already done.
set -uo pipefail

REPO_URL="https://github.com/antonizick/NX-Task-Manager.git"
APP_DIR="/var/www/nxtm"
DB_NAME="antonizi_nxtask"
DB_USER="nxtm"
DB_HOST="127.0.0.1"
TZ_VALUE="$(timedatectl show -p Timezone --value 2>/dev/null || cat /etc/timezone 2>/dev/null || echo UTC)"
LOG_FILE="/tmp/nxtm-install-$(date +%Y%m%d-%H%M%S).log"
RESET_DB=0
ASSUME_YES=0
DB_PASSWORD=""

for arg in "$@"; do
  case "$arg" in
    --reset-db) RESET_DB=1 ;;
    --yes|-y) ASSUME_YES=1 ;;
    --password=*) DB_PASSWORD="${arg#*=}" ;;
    --app-dir=*) APP_DIR="${arg#*=}" ;;
    -h|--help)
      cat <<EOF
Usage: $0 [--reset-db] [--yes] [--password=SECRET] [--app-dir=/path]

  --reset-db        Drop and re-import the database schema/seed (destructive)
  --yes             Assume "yes" for confirmation prompts
  --password=SECRET Use this DB password instead of auto-generating one
  --app-dir=DIR     Install location (default: /var/www/nxtm)
EOF
      exit 0
      ;;
  esac
done

# ---------- output helpers ----------
if [ -t 1 ]; then
  BOLD=$(tput bold); RESET=$(tput sgr0)
  GREEN=$(tput setaf 2); YELLOW=$(tput setaf 3); RED=$(tput setaf 1); BLUE=$(tput setaf 4)
else
  BOLD=""; RESET=""; GREEN=""; YELLOW=""; RED=""; BLUE=""
fi

STEP_N=0
step()  { STEP_N=$((STEP_N + 1)); echo; echo "${BOLD}${BLUE}Step ${STEP_N}: $1${RESET}"; echo "----------------------------------------------------------------"; }
info()  { echo "  $*"; }
ok()    { echo "  ${GREEN}✓${RESET} $*"; }
warn()  { echo "  ${YELLOW}!${RESET} $*"; }
fail()  { echo "  ${RED}✗${RESET} $*"; }

confirm() {
  # confirm "question" -> 0 (yes) or 1 (no). Defaults to yes.
  [ "$ASSUME_YES" = 1 ] && return 0
  read -rp "  $1 [Y/n] " reply
  [ -z "$reply" ] || [[ "$reply" =~ ^[Yy] ]]
}

# Runs a command, logs full output, shows a friendly result. On failure,
# offers retry/skip/abort instead of just dying — most failures here are
# transient (apt lock, network hiccup) or safely skippable.
run() {
  local desc="$1"; shift
  printf '  - %s... ' "$desc"
  if "$@" >>"$LOG_FILE" 2>&1; then
    echo "done"
    return 0
  fi
  echo "${RED}FAILED${RESET}"
  warn "Last 15 lines of output (full log: $LOG_FILE):"
  tail -15 "$LOG_FILE" | sed 's/^/      /'

  if [ ! -t 0 ]; then
    fail "No terminal to prompt on (stdin isn't interactive) — aborting. Log at $LOG_FILE"
    exit 1
  fi

  while true; do
    read -rp "  [r]etry / [s]kip / [a]bort? " choice
    case "$choice" in
      r|R)
        printf '  - retrying %s... ' "$desc"
        if "$@" >>"$LOG_FILE" 2>&1; then
          echo "done"; return 0
        fi
        echo "${RED}FAILED${RESET}"
        warn "Last 15 lines of output (full log: $LOG_FILE):"
        tail -15 "$LOG_FILE" | sed 's/^/      /'
        ;;
      s|S) warn "Skipped: $desc — later steps may fail because of this."; return 1 ;;
      a|A) fail "Aborting. Log at $LOG_FILE"; exit 1 ;;
      *) echo "  please type r, s, or a" ;;
    esac
  done
}

echo "${BOLD}NXTM local installer${RESET}"
echo "Log: $LOG_FILE"
: >"$LOG_FILE"

# ---------- Step 1: preflight ----------
step "Preflight checks"
if ! command -v apt >/dev/null 2>&1; then
  fail "This script targets Ubuntu/Debian (needs apt). Follow the manual steps in README.md instead."
  exit 1
fi
ok "apt found"

if ! sudo -v; then
  fail "Need sudo access to install packages and configure services."
  exit 1
fi
ok "sudo access confirmed"

if grep -qi microsoft /proc/version 2>/dev/null; then
  info "Detected WSL — services don't start on boot here, so this script starts them explicitly."
fi

# ---------- Step 2: packages ----------
step "Install Apache, PHP, and MySQL"
run "apt update" sudo apt update -y
run "install apache2, mysql-server, php, and required extensions" \
  sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysqli php-cli git

if mysql --version 2>/dev/null | grep -qi mariadb; then
  fail "MariaDB is installed instead of MySQL. NXTM's schema uses utf8mb4_0900_ai_ci,"
  fail "which is MySQL-8-specific and doesn't exist on MariaDB. Remove mariadb-server and"
  fail "install mysql-server instead, then re-run this script."
  exit 1
fi
ok "MySQL (not MariaDB) confirmed"

# ---------- Step 3: services ----------
step "Start services"
sudo service mysql status >/dev/null 2>&1 || run "start mysql" sudo service mysql start
mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null && ok "mysql is running" || warn "mysql may not be responding yet"

sudo service apache2 status >/dev/null 2>&1 || run "start apache2" sudo service apache2 start
ok "apache2 running"

# ---------- Step 4: get the code ----------
step "Get the code"
SELF_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SELF_DIR/dbcon.php" ] && [ -f "$SELF_DIR/db/schema.sql" ]; then
  info "Running from inside an existing checkout ($SELF_DIR) — using it."
  APP_DIR="$SELF_DIR"
elif [ -d "$APP_DIR/.git" ]; then
  info "$APP_DIR already exists — leaving it as-is (not pulling, to avoid clobbering local edits)."
else
  sudo mkdir -p "$APP_DIR"
  sudo chown "$USER:$USER" "$APP_DIR"
  run "clone repository into $APP_DIR" git clone "$REPO_URL" "$APP_DIR"
fi
cd "$APP_DIR" || { fail "Can't cd into $APP_DIR"; exit 1; }
ok "working in $APP_DIR"

# ---------- Step 5: database ----------
step "Create the database"
DBCON_CONFIGURED=0
if [ -f dbcon.php ] && ! grep -q '< user name >' dbcon.php; then
  DBCON_CONFIGURED=1
fi

if [ -z "$DB_PASSWORD" ] && [ "$DBCON_CONFIGURED" = 1 ]; then
  # Re-run on an already-configured install: reuse the password already in
  # dbcon.php instead of generating a new one. Otherwise ALTER USER below
  # would rotate the DB password out from under a dbcon.php we're not
  # touching (dbcon.php is only rewritten once, on first setup) and the
  # app would break with a stale-password "Access denied" on next load.
  DB_PASSWORD="$(php -r '
    $c = file_get_contents("dbcon.php");
    if (preg_match("/\\\$password\s*=\s*'"'"'([^'"'"']*)'"'"'/", $c, $m)) echo $m[1];
  ' 2>/dev/null)"
  [ -n "$DB_PASSWORD" ] && info "Reusing the DB password already configured in dbcon.php."
fi

if [ -z "$DB_PASSWORD" ]; then
  DB_PASSWORD="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9')"
  info "Generated a random DB password (shown again in the summary at the end)."
fi

DB_EXISTS=$(sudo mysql -Nse "SHOW DATABASES LIKE '${DB_NAME}';" 2>>"$LOG_FILE")
if [ -n "$DB_EXISTS" ] && [ "$RESET_DB" = 1 ]; then
  if confirm "Database '$DB_NAME' exists. --reset-db will DROP it and lose all data. Continue?"; then
    run "drop existing database" sudo mysql -e "DROP DATABASE ${DB_NAME};"
    DB_EXISTS=""
  else
    warn "Keeping existing database; ignoring --reset-db."
  fi
fi

if [ -z "$DB_EXISTS" ]; then
  run "create database" sudo mysql -e \
    "CREATE DATABASE ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
else
  ok "database '$DB_NAME' already exists — leaving it as-is"
fi

# Host must be 127.0.0.1, not localhost: MySQL treats 'user'@'localhost' as
# socket-only, and the import below (and dbcon.php) connect over TCP —
# a 'localhost'-scoped account can never authenticate over that connection.
run "create/update DB user" sudo mysql -e "
CREATE USER IF NOT EXISTS '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'${DB_HOST}';
FLUSH PRIVILEGES;
"

TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" -Nse \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" 2>>"$LOG_FILE")
if [ "${TABLE_COUNT:-0}" -gt 0 ] && [ "$RESET_DB" != 1 ]; then
  ok "database already has $TABLE_COUNT tables — skipping schema/seed import (use --reset-db to force)"
else
  run "import schema" mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "source db/schema.sql"
  run "import seed data" mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "source db/seed.sql"
fi

# ---------- Step 6: dbcon.php ----------
step "Configure dbcon.php"
if grep -q '< user name >' dbcon.php 2>/dev/null; then
  cp dbcon.php dbcon.php.bak
  DB_HOST="$DB_HOST" DB_USER="$DB_USER" DB_PASSWORD="$DB_PASSWORD" DB_NAME="$DB_NAME" php -r '
    $f = "dbcon.php";
    $c = file_get_contents($f);
    $c = preg_replace("/\\\$host\s*=.*;/",     "\$host = " . var_export(getenv("DB_HOST"), true) . ";", $c);
    $c = preg_replace("/\\\$user\s*=.*;/",     "\$user = " . var_export(getenv("DB_USER"), true) . ";", $c);
    $c = preg_replace("/\\\$password\s*=.*;/", "\$password = " . var_export(getenv("DB_PASSWORD"), true) . ";", $c);
    $c = preg_replace("/\\\$database\s*=.*;/", "\$database = " . var_export(getenv("DB_NAME"), true) . ";", $c);
    file_put_contents($f, $c);
  '
  ok "dbcon.php updated (original backed up to dbcon.php.bak)"
else
  ok "dbcon.php already configured — leaving it as-is"
fi

# ---------- Step 7: php.ini ----------
step "Match PHP's error reporting to production"
PHP_INI=$(sudo find /etc/php -path '*/apache2/php.ini' 2>/dev/null | head -1)
if [ -z "$PHP_INI" ]; then
  warn "Couldn't locate the Apache SAPI php.ini automatically — skipping. See README Step 5 to do it by hand."
else
  [ -f "${PHP_INI}.nxtm-orig" ] || sudo cp "$PHP_INI" "${PHP_INI}.nxtm-orig"
  set_ini() {
    local key="$1" val="$2"
    if sudo grep -qE "^${key}\s*=" "$PHP_INI"; then
      sudo sed -i "s#^${key}\s*=.*#${key} = ${val}#" "$PHP_INI"
    else
      echo "${key} = ${val}" | sudo tee -a "$PHP_INI" >/dev/null
    fi
  }
  set_ini "error_reporting" "E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED"
  set_ini "expose_php" "Off"
  set_ini "post_max_size" "60M"
  set_ini "upload_max_filesize" "60M"
  set_ini "memory_limit" "3000M"
  set_ini "max_execution_time" "120"
  set_ini "session.save_path" "/tmp"
  set_ini "date.timezone" "$TZ_VALUE"
  ok "php.ini updated ($PHP_INI, original saved as ${PHP_INI}.nxtm-orig)"
fi

# ---------- Step 8: permissions ----------
step "Let the web server write to data/"
run "chown data/ to www-data" sudo chown -R www-data:www-data "$APP_DIR/data"

# ---------- Step 9: apache vhost ----------
step "Serve the app"
VHOST=/etc/apache2/sites-available/000-default.conf
run "point DocumentRoot at $APP_DIR" sudo sed -i "s#DocumentRoot .*#DocumentRoot ${APP_DIR}#" "$VHOST"

# Ubuntu's default apache2.conf sets AllowOverride None for /var/www — without
# this, data/.htaccess (which blocks direct access to users.json) is silently
# ignored and the credential store is served as plain text. Confirmed with a
# live test: without this block, GET /data/users.json returns 200; with it, 403.
if ! sudo grep -q "Directory ${APP_DIR}>" "$VHOST"; then
  printf '<Directory %s>\n    AllowOverride All\n</Directory>\n' "$APP_DIR" | sudo tee -a "$VHOST" >/dev/null
  ok "added AllowOverride All for $APP_DIR (needed for .htaccess to take effect)"
else
  ok "AllowOverride block already present"
fi

run "enable mod_rewrite" sudo a2enmod rewrite
run "restart apache2" sudo service apache2 restart

# ---------- summary ----------
echo
echo "${BOLD}${GREEN}NXTM is installed.${RESET}"
echo
echo "  App directory:  $APP_DIR"
echo "  DB name:         $DB_NAME"
echo "  DB user:         $DB_USER@$DB_HOST"
echo "  DB password:     $DB_PASSWORD"
echo "  (saved in dbcon.php — write it down elsewhere too if you'll need it again)"
echo
echo "Next: open ${BOLD}http://localhost/${RESET} in your Windows browser."
echo "First visit lands on setup.php — pick a username/password, then scan the"
echo "QR code with an authenticator app. No phone handy? Run:"
echo "  sudo apt install -y oathtool"
echo "  oathtool --totp -b <secret-from-the-page>"
echo
echo "Full log: $LOG_FILE"
