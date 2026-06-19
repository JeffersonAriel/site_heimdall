#!/bin/bash
# ============================================================
#  HEIMDALL ERP — Script de Deploy para HostGator
# ============================================================

APPPATH=$(pwd)
PUBHTML="$HOME/public_html"

echo "==> Deploy iniciado em: $APPPATH"
echo "==> public_html: $PUBHTML"

# --- Copiar bootstrap para public_html ---
cp "$APPPATH/public_html_index.php" "$PUBHTML/index.php" && echo "OK: index.php copiado" || echo "WARN: falha ao copiar index.php"
cp "$APPPATH/public_html_htaccess" "$PUBHTML/.htaccess" && echo "OK: .htaccess copiado" || echo "WARN: falha ao copiar .htaccess"
cp "$APPPATH/public/install.php" "$PUBHTML/install.php" 2>/dev/null && echo "OK: install.php copiado" || true
cp -R "$APPPATH/public/build" "$PUBHTML/" 2>/dev/null && echo "OK: build copiado" || true

# --- Permissoes ---
touch "$APPPATH/.env" && chmod 664 "$APPPATH/.env" && echo "OK: .env criado e permissao definida"
chmod -R 775 "$APPPATH/storage" && echo "OK: storage 775"
chmod -R 775 "$APPPATH/bootstrap/cache" && echo "OK: bootstrap/cache 775"

# --- Composer install ---
if [ -f /usr/local/bin/composer ]; then
    echo "==> Rodando composer install..."
    /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$APPPATH" && echo "OK: composer install" || echo "WARN: composer install falhou"
elif command -v composer &> /dev/null; then
    echo "==> Rodando composer install (PATH)..."
    composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$APPPATH" && echo "OK: composer install" || echo "WARN: composer install falhou"
else
    echo "WARN: composer nao encontrado"
fi

# --- Limpar caches do Laravel ---
/usr/local/bin/php "$APPPATH/artisan" config:clear 2>/dev/null && echo "OK: config:clear" || true
/usr/local/bin/php "$APPPATH/artisan" route:clear  2>/dev/null && echo "OK: route:clear"  || true
/usr/local/bin/php "$APPPATH/artisan" view:clear   2>/dev/null && echo "OK: view:clear"   || true

echo "==> Deploy concluido!"
