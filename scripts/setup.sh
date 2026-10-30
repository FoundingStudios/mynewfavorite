#!/usr/bin/env bash
# Bootstraps WordPress + WooCommerce + WPGraphQL + WooGraphQL in a Codespace.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_DIR="$ROOT/wordpress"
DB_NAME=wp; DB_USER=wp; DB_PASS=wp

SITE_URL="http://localhost:8080"
if [ -n "${CODESPACE_NAME:-}" ]; then
  SITE_URL="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
fi
ADMIN_PASS="${WP_ADMIN_PASSWORD:-$(openssl rand -base64 12)}"

# --- Tooling -------------------------------------------------------------
if ! command -v wp >/dev/null; then
  curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /tmp/wp
  chmod +x /tmp/wp && sudo mv /tmp/wp /usr/local/bin/wp
fi
php -m | grep -qi mysqli || sudo docker-php-ext-install mysqli

if ! command -v mariadbd >/dev/null && ! command -v mysqld >/dev/null; then
  sudo apt-get update && sudo apt-get install -y mariadb-server
fi
sudo service mariadb start || sudo service mysql start
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;
  CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
  GRANT ALL ON $DB_NAME.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

# --- WordPress core ------------------------------------------------------
mkdir -p "$WP_DIR" && cd "$WP_DIR"
[ -f wp-load.php ]   || wp core download
[ -f wp-config.php ] || wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --skip-check
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw
wp config set WP_DEBUG_DISPLAY false --raw
wp core is-installed 2>/dev/null || wp core install \
  --url="$SITE_URL" --title="Headless Store" \
  --admin_user=admin --admin_password="$ADMIN_PASS" \
  --admin_email=admin@example.com --skip-email
wp rewrite structure '/%postname%/'

# --- 1. Core installs ----------------------------------------------------
wp plugin install woocommerce wp-graphql --activate

# --- 2. WooGraphQL (not on wordpress.org; installed from GitHub release) --
wp plugin install \
  https://github.com/wp-graphql/wp-graphql-woocommerce/releases/latest/download/wp-graphql-woocommerce.zip \
  --activate

# --- 3. Custom plugin (symlinked so it stays in the repo) ------------------
ln -sfn "$ROOT/plugins/headless-commerce-engine" wp-content/plugins/headless-commerce-engine
wp plugin activate headless-commerce-engine
wp rewrite flush

echo
echo "Done. Admin login: admin / $ADMIN_PASS   (save this now)"
echo "Start the site:  cd wordpress && wp server --host=0.0.0.0 --port=8080"
echo "Then run:        bash scripts/diagnose.sh"
