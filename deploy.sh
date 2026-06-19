#!/bin/bash
# ============================================================
#  HEIMDALL ERP — Script de Deploy para HostGator
# ============================================================

APPPATH=$(pwd)
PUBHTML="$HOME/public_html"

echo "==> Deploy iniciado em: $APPPATH"
echo "==> public_html: $PUBHTML"

# --- Copiar bootstrap para public_html ---
cp -f "$APPPATH/public_html_index.php" "$PUBHTML/index.php" && echo "OK: index.php copiado" || echo "WARN: falha ao copiar index.php"
cp -f "$APPPATH/public_html_htaccess" "$PUBHTML/.htaccess" && echo "OK: .htaccess copiado" || echo "WARN: falha ao copiar .htaccess"
cp -f "$APPPATH/public/install.php" "$PUBHTML/install.php" && echo "OK: install.php copiado" || echo "WARN: falha ao copiar install.php"
cp -f "$APPPATH/gitfix.php" "$PUBHTML/gitfix.php" && echo "OK: gitfix.php copiado" || echo "WARN: falha ao copiar gitfix.php"
cp -Rf "$APPPATH/public/build" "$PUBHTML/" && echo "OK: build copiado" || echo "WARN: falha ao copiar build"

# --- Permissoes ---
touch "$APPPATH/.env" && chmod 664 "$APPPATH/.env" && echo "OK: .env criado e permissao definida"
chmod -R 775 "$APPPATH/storage" && echo "OK: storage 775"
chmod -R 775 "$APPPATH/bootstrap/cache" && echo "OK: bootstrap/cache 775"

# --- Composer install ---
COMPOSER_BIN=""
for path in /usr/local/bin/composer /usr/bin/composer /opt/cpanel/ea-php82/root/usr/bin/composer; do
    if [ -f "$path" ]; then COMPOSER_BIN="$path"; break; fi
done
if [ -z "$COMPOSER_BIN" ] && command -v composer &> /dev/null; then
    COMPOSER_BIN="composer"
fi

if [ -n "$COMPOSER_BIN" ]; then
    echo "==> Rodando composer: $COMPOSER_BIN"
    $COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction --working-dir="$APPPATH" && echo "OK: composer install" || echo "WARN: composer install falhou"
else
    echo "==> Composer nao encontrado, baixando..."
    /usr/local/bin/php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    /usr/local/bin/php composer-setup.php --quiet
    rm -f composer-setup.php
    /usr/local/bin/php composer.phar install --no-dev --optimize-autoloader --no-interaction --working-dir="$APPPATH" && echo "OK: composer install" || echo "WARN: composer install falhou"
    rm -f "$APPPATH/composer.phar"
fi

# --- Limpar caches do Laravel ---
/usr/local/bin/php "$APPPATH/artisan" config:clear 2>/dev/null && echo "OK: config:clear" || true
/usr/local/bin/php "$APPPATH/artisan" route:clear  2>/dev/null && echo "OK: route:clear"  || true
/usr/local/bin/php "$APPPATH/artisan" view:clear   2>/dev/null && echo "OK: view:clear"   || true

echo "==> Deploy concluido!"
