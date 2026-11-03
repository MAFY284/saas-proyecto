#!/bin/bash
set -e

APP_DIR=/var/www/html
HOST_UID="${HOST_UID:-1000}"
HOST_GID="${HOST_GID:-1000}"

echo "[entrypoint] PHP $(php -r 'echo PHP_VERSION;') | host ${HOST_UID}:${HOST_GID}"

groupmod -o -g "${HOST_GID}" www-data 2>/dev/null || true
usermod -o -u "${HOST_UID}" www-data 2>/dev/null || true

mkdir -p /var/log/apache2 /var/run/apache2 /var/lock/apache2
chown -R www-data:www-data /var/log/apache2 /var/run/apache2 /var/lock/apache2 || true

echo "[entrypoint] waiting for MariaDB..."
for i in $(seq 1 30); do
  if php -r "try { new PDO('mysql:host=db;port=3306', 'root', 'root'); exit(0); } catch (Throwable \$e) { exit(1); }" 2>/dev/null; then
    echo "[entrypoint] MariaDB ready"
    break
  fi
  sleep 1
done

if [ ! -d "$APP_DIR" ]; then
  echo "[entrypoint] project directory missing, creating it..."
  mkdir -p "$APP_DIR"
  chown www-data:www-data "$APP_DIR"
fi

# Si project/ ya existe, no se recrea Laravel. vendor/ no se sube a GitHub,
# así que se reconstruye con composer install cuando falta.
if [ -f "$APP_DIR/artisan" ] && [ -f "$APP_DIR/composer.json" ]; then
  echo "[entrypoint] project already generated; skipping Laravel create-project"
elif [ -f "$APP_DIR/composer.json" ] || [ -f "$APP_DIR/package.json" ] || [ -d "$APP_DIR/app" ]; then
  echo "[entrypoint] project directory already has application files; skipping Laravel create-project"
else
  echo "[entrypoint] creating Laravel starter in ../project (first run)..."
  rm -rf /tmp/laravel-app
  mkdir -p /tmp/laravel-app
  chown www-data:www-data /tmp/laravel-app
  gosu www-data composer create-project --prefer-dist laravel/laravel /tmp/laravel-app
  cp -a /tmp/laravel-app/. "$APP_DIR"/
  rm -rf /tmp/laravel-app
  echo "[entrypoint] Laravel installed"
fi

if [ ! -f "$APP_DIR/.env" ] && [ -f "$APP_DIR/.env.example" ]; then
  echo "[entrypoint] creating .env from .env.example..."
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  chown www-data:www-data "$APP_DIR/.env"
fi

if [ -d "$APP_DIR/storage" ]; then
  chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
  chmod -R ug+rwx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
fi

if [ -f "$APP_DIR/composer.json" ] && [ ! -f "$APP_DIR/vendor/autoload.php" ]; then
  echo "[entrypoint] composer install (reconstruir vendor/; no se sube a GitHub)..."
  gosu www-data composer install --no-interaction --prefer-dist --working-dir="$APP_DIR"
elif [ -f "$APP_DIR/vendor/autoload.php" ]; then
  echo "[entrypoint] vendor already present"
fi

[ -f "$APP_DIR/artisan" ] && chmod 775 "$APP_DIR/artisan" || true
[ -f "$APP_DIR/.env" ] && chown www-data:www-data "$APP_DIR/.env" || true

if [ -f "$APP_DIR/.env" ]; then
  sed -i \
    -e 's/^DB_CONNECTION=.*/DB_CONNECTION=mariadb/' \
    -e 's/^#\? *DB_HOST=.*/DB_HOST=db/' \
    -e 's/^#\? *DB_PORT=.*/DB_PORT=3306/' \
    -e 's/^#\? *DB_DATABASE=.*/DB_DATABASE=laraveldb/' \
    -e 's/^#\? *DB_USERNAME=.*/DB_USERNAME=root/' \
    -e 's/^#\? *DB_PASSWORD=.*/DB_PASSWORD=root/' \
    -e 's|^APP_URL=.*|APP_URL=http://localhost:8080|' \
    "$APP_DIR/.env"

  cd "$APP_DIR"

  if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    gosu www-data php artisan key:generate --force || true
  fi

  gosu www-data php artisan migrate --force \
    || echo "[entrypoint] migrate skipped"

  gosu www-data php artisan config:clear || true
fi

if [ -f "$APP_DIR/package.json" ]; then
  cd "$APP_DIR"
  mkdir -p /tmp/npm-cache
  export npm_config_cache=/tmp/npm-cache
  export HOME=/tmp

  if [ ! -d "$APP_DIR/node_modules/vite" ]; then
    echo "[entrypoint] npm install (generar node_modules, sin recrear Laravel)..."
    set +e
    if [ -f "$APP_DIR/package-lock.json" ]; then
      npm ci
    else
      npm install
    fi
    NPM_INSTALL_STATUS=$?
    set -e
    chown -R www-data:www-data "$APP_DIR/node_modules" 2>/dev/null || true
    if [ "$NPM_INSTALL_STATUS" -ne 0 ]; then
      echo "[entrypoint] WARNING: npm install failed — Apache will start anyway"
    fi
  else
    echo "[entrypoint] node_modules already present"
    NPM_INSTALL_STATUS=0
  fi

  if [ "$NPM_INSTALL_STATUS" -eq 0 ] && [ ! -f "$APP_DIR/public/build/manifest.json" ]; then
    echo "[entrypoint] npm run build (generar public/build)..."
    set +e
    npm run build
    NPM_BUILD_STATUS=$?
    set -e
    chown -R www-data:www-data "$APP_DIR/public/build" 2>/dev/null || true
    if [ "$NPM_BUILD_STATUS" -eq 0 ]; then
      echo "[entrypoint] Tailwind/Vite build ready"
    else
      echo "[entrypoint] WARNING: npm run build failed — Apache will start anyway"
    fi
  elif [ -f "$APP_DIR/public/build/manifest.json" ]; then
    echo "[entrypoint] Tailwind assets already built"
  fi
fi

echo "[entrypoint] starting Apache on :80 (host :8080)"
exec apache2-foreground
