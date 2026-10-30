#!/usr/bin/env bash
# Bootstraps WordPress + WooCommerce + WPGraphQL + WooGraphQL in a Codespace. Safe to re-run.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_DIR="$ROOT/wordpress"
DB_NAME=wp; DB_USER=wp; DB_PASS=wp
NEW_INSTALL=0

# Public address of the forwarded port (localhost outside Codespaces)
SITE_URL="http://localhost:8080"
if [ -n "${CODESPACE_NAME:-}" ]; then
  SITE_URL="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
fi

# PHP settings: WP-CLI needs more than 128 MB; Xdebug only adds noise
sudo mkdir -p /usr/local/etc/php/conf.d
printf 'memory_limit=512M\nxdebug.mode=off\n' | sudo tee /usr/local/etc/php/conf.d/zz-wp.ini >/dev/null

# System packages
NEED=""
command -v git-lfs >/dev/null || NEED="$NEED git-lfs"
{ command -v mariadbd >/dev/null || command -v mysqld >/dev/null; } || NEED="$NEED mariadb-server"
if [ -n "$NEED" ]; then sudo apt-get update && sudo apt-get install -y $NEED; fi

php -r 'exit(extension_loaded("mysqli") ? 0 : 1);' || sudo env PHP_INI_DIR=/usr/local/etc/php docker-php-ext-install mysqli

if ! command -v wp >/dev/null; then
  curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /tmp/wp
  chmod +x /tmp/wp && sudo mv /tmp/wp /usr/local/bin/wp
fi

# Database
sudo service mariadb start || sudo service mysql start
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;
  CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
  CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
  GRANT ALL ON $DB_NAME.* TO '$DB_USER'@'localhost';
  GRANT ALL ON $DB_NAME.* TO '$DB_USER'@'127.0.0.1'; FLUSH PRIVILEGES;"

# WordPress core
mkdir -p "$WP_DIR" && cd "$WP_DIR"
[ -f wp-load.php ]   || wp core download
[ -f wp-config.php ] || wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost=127.0.0.1 --skip-check
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw
wp config set WP_DEBUG_DISPLAY false --raw
sed -i "/define( 'WP_HOME'/d" wp-config.php
wp config set WP_HOME "$SITE_URL"
wp config set WP_SITEURL "$SITE_URL"
# Trust the Codespaces HTTPS proxy
grep -q X_FORWARDED_PROTO wp-config.php || sed -i "2i if ( isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) \&\& \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) { \$_SERVER['HTTPS'] = 'on'; }" wp-config.php

if ! wp core is-installed 2>/dev/null; then
  ADMIN_PASS="${WP_ADMIN_PASSWORD:-$(openssl rand -base64 12)}"
  wp core install --url="$SITE_URL" --title="Headless Store" \
    --admin_user=admin --admin_password="$ADMIN_PASS" \
    --admin_email=admin@example.com --skip-email
  NEW_INSTALL=1
fi
wp option update home "$SITE_URL"
wp option update siteurl "$SITE_URL"
wp rewrite structure '/%postname%/'

# Plugins (only installs what is missing)
for p in woocommerce wp-graphql; do wp plugin is-installed $p || wp plugin install $p; done
wp plugin is-installed wp-graphql-woocommerce || wp plugin install https://github.com/wp-graphql/wp-graphql-woocommerce/releases/latest/download/wp-graphql-woocommerce.zip
wp plugin activate woocommerce wp-graphql wp-graphql-woocommerce
ln -sfn "$ROOT/plugins/headless-commerce-engine" wp-content/plugins/headless-commerce-engine
wp plugin activate headless-commerce-engine
wp rewrite flush

echo
if [ "$NEW_INSTALL" = "1" ]; then
  echo "Done. Admin login: admin / $ADMIN_PASS   (save this now)"
else
  echo "Done. Existing site kept (admin password unchanged)."
fi
echo "Site address: $SITE_URL"
echo "Start the site:  cd wordpress && wp server --host=0.0.0.0 --port=8080"
echo "Then run:        bash scripts/diagnose.sh"
