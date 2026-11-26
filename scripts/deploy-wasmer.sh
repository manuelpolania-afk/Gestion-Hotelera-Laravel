#!/usr/bin/env bash
#
# Despliega el aplicativo Laravel en Wasmer con un solo comando:
#
#   bash scripts/deploy-wasmer.sh            # compila y despliega
#   DRY_RUN=1 bash scripts/deploy-wasmer.sh  # solo arma el paquete, no despliega
#
# Arma una carpeta limpia (.wasmer-build/) sin .env, logs ni node_modules,
# instala las dependencias de producción, compila los assets y la publica
# como una versión nueva. La API, la web y el APK usan el mismo servidor,
# así que el cambio se ve en todos a la vez.
#
# Variables opcionales:
#   WASMER_TOKEN   token de Wasmer (en GitHub Actions); en local usa "wasmer login".
#   SKIP_ASSETS=1  no ejecuta "npm ci && npm run build" (usa public/build existente).
#   BUILD_DIR      carpeta temporal (por defecto .wasmer-build).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD="${BUILD_DIR:-$ROOT/.wasmer-build}"
WASMER="${WASMER_BIN:-wasmer}"

step() { printf '\n\033[1;33m==> %s\033[0m\n' "$1"; }

cd "$ROOT"

step "Compilando assets (Vite)"
if [[ "${SKIP_ASSETS:-0}" != "1" ]]; then
    npm ci --no-audit --no-fund
    npm run build
fi
[[ -f public/build/manifest.json ]] || { echo "Falta public/build/manifest.json: ejecuta npm run build" >&2; exit 1; }

step "Armando paquete limpio en $BUILD"
rm -rf "$BUILD"
mkdir -p "$BUILD"
for item in app bootstrap config database public resources routes storage artisan composer.json composer.lock wasmer.toml app.yaml; do
    cp -R "$item" "$BUILD/"
done
[[ -d lang ]] && cp -R lang "$BUILD/"

# Nada local ni secreto viaja al servidor
rm -f "$BUILD"/database/*.sqlite "$BUILD"/bootstrap/cache/*.php
rm -rf "$BUILD/public/storage" "$BUILD/public/hot"
find "$BUILD/storage/logs" "$BUILD/storage/framework/cache" "$BUILD/storage/framework/sessions" \
     "$BUILD/storage/framework/views" "$BUILD/storage/framework/testing" \
     -type f ! -name '.gitignore' -delete 2>/dev/null || true

step "Instalando dependencias de producción (composer --no-dev)"
composer install --working-dir="$BUILD" --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Versión única por despliegue: 0.2.AAAAMMDDHHMM
VERSION="0.2.$(date -u +%Y%m%d%H%M)"
sed -i.bak -E "s/^version = \".*\"/version = \"$VERSION\"/" "$BUILD/wasmer.toml" && rm -f "$BUILD/wasmer.toml.bak"
echo "Versión del paquete: $VERSION"

if [[ "${DRY_RUN:-0}" == "1" ]]; then
    step "DRY_RUN=1: paquete listo en $BUILD (no se desplegó)"
    exit 0
fi

step "Desplegando en Wasmer"
cd "$BUILD"
TOKEN_ARGS=()
[[ -n "${WASMER_TOKEN:-}" ]] && TOKEN_ARGS=(--token "$WASMER_TOKEN")
"$WASMER" deploy --non-interactive --publish-package "${TOKEN_ARGS[@]}"

step "Comprobando el servidor"
URL="https://hotel-laravel-manuel19.wasmer.app/api/v1/portada"
for intento in 1 2 3 4 5 6; do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 "$URL" || true)"
    if [[ "$code" == "200" ]]; then
        echo "OK: $URL responde 200 (versión $VERSION)"
        exit 0
    fi
    echo "Intento $intento: respondió $code, reintentando en 10 s..."
    sleep 10
done
echo "El despliegue terminó pero $URL no respondió 200. Revisa https://wasmer.io/apps/manuel19/hotel-laravel" >&2
exit 1
