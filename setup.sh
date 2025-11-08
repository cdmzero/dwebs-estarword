#!/bin/sh
set -e

# Script de arranque desde cero.
# Requiere tener curl, jq, composer y PHP disponibles en el sistema.
# Uso: ./setup.sh [install|env|migrate|serve|test|full|stop]
PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_ROOT"

log() {
  printf "\033[1;34m==>%s\033[0m\n" " $1"
}

# 1. Instalación de dependencias
action_install() {
  log "Instalando dependencias PHP"
  composer install

  if command -v npm >/dev/null 2>&1; then
    log "Instalando dependencias Node"
    npm install
  else
    log "npm no está disponible, saltando instalación de Node"
  fi
}

# 2. Preparar entorno
action_env() {
  log "Copiando .env desde .env.example"
  cp .env.example .env

  if grep -q "CLOUDINARY" .env; then
    if ! grep -q "CLOUDINARY_URL" .env || [ -z "${CLOUDINARY_URL:-}" ]; then
      log "⚠️  Configura las variables CLOUDINARY_* en tu .env antes de subir imágenes"
    fi
  fi

  log "Generando APP_KEY"
  php artisan key:generate --force

  if [ ! -f database/database.sqlite ]; then
    log "Creando base de datos SQLite"
    mkdir -p database
    touch database/database.sqlite
  fi
}

# 3. Migraciones y seeders
action_migrate() {
  log "Ejecutando migrate:fresh --seed"
  php artisan migrate:fresh --seed
}

# 4. Arrancar servidor
action_serve() {
  log "Iniciando php artisan serve"
  php artisan serve --host=127.0.0.1 --port=8000 > storage/logs/setup_server.log 2>&1 &
  SERVER_PID=$!
  log "Servidor en background con PID $SERVER_PID"
  echo "$SERVER_PID" > storage/setup_server.pid
  sleep 2
}

# 5. Obtener tokens y probar API
action_test_api() {
  log "Reiniciando base de datos para pruebas"
  php artisan migrate:fresh --seed

  log "Generando tokens de prueba"
  ADMIN_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/tokens \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@example.com","password":"password"}' | jq -r .token)

  MANAGER_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/tokens \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"manager@example.com","password":"password"}' | jq -r .token)

  USER_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/tokens \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"user@example.com","password":"password"}' | jq -r .token)

  printf "ADMIN_TOKEN=%s\nMANAGER_TOKEN=%s\nUSER_TOKEN=%s\n" "$ADMIN_TOKEN" "$MANAGER_TOKEN" "$USER_TOKEN"

  log "Listar naves como user"
  curl -s http://127.0.0.1:8000/api/naves \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $USER_TOKEN" | jq

  log "Listar mantenimientos de la nave 1 como user"
  curl -s http://127.0.0.1:8000/api/naves/1/maintenances \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $USER_TOKEN" | jq

  log "Listar listados globales como user"
  curl -s http://127.0.0.1:8000/api/lists \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $USER_TOKEN" | jq

  log "Intento fallido de crear nave como manager"
  curl -s -o /tmp/manager_fail.json -w "%{http_code}\n" -X POST http://127.0.0.1:8000/api/naves \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $MANAGER_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"planet_id":1,"name":"Manager Ship","model":"M2","crew":3,"passengers":8,"type":"cargo"}'
  tail -n 1 /tmp/manager_fail.json

  log "Crear mantenimiento como manager"
  curl -s -X POST http://127.0.0.1:8000/api/naves/1/maintenances \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $MANAGER_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"date_planned":"2026-07-01 09:00:00","description":"Chequeo manager","cost":1200}' | jq

  log "Crear nave como admin"
  UNIQUE_NAME="Supernave Admin $(date +%s)"
  curl -s -X POST http://127.0.0.1:8000/api/naves \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $ADMIN_TOKEN" \
    -H "Content-Type: application/json" \
    -d "{\"planet_id\":1,\"name\":\"$UNIQUE_NAME\",\"model\":\"Super\",\"crew\":99,\"passengers\":500,\"type\":\"dreadnought\"}" | jq

  log "Ejecutando pruebas unitarias de mantenimiento"
  php artisan test --filter=MaintenanceTest

  log "Ejecutando pruebas funcionales de API"
  php artisan test --filter=ApiCrudTest
}

# 6. Detener servidor
action_stop_server() {
  if [ -f storage/setup_server.pid ]; then
    SERVER_PID=$(cat storage/setup_server.pid)
    log "Deteniendo servidor PID $SERVER_PID"
    kill "$SERVER_PID" 2>/dev/null || true
    rm storage/setup_server.pid
  fi
}

case "$1" in
  install)
    action_install
    ;;
  env)
    action_env
    ;;
  migrate)
    action_env
    action_migrate
    ;;
  serve)
    action_env
    action_serve
    ;;
  test)
    action_env
    action_serve
    action_test_api
    action_stop_server
    ;;
  full|"")
    action_install
    action_env
    action_migrate
    echo "¿Deseas ejecutar los tests y llamadas de verificación? (s/N)"
    read -r RUN_TESTS
    case "$RUN_TESTS" in
      s|S|si|SI|Si)
        action_serve
        action_test_api
        action_stop_server
        action_migrate
        log "Base reseteada tras las pruebas para regenerar tokens demo"
        ;;
      *)
        echo "Omitiendo pruebas."
        ;;
    esac
    ;;
  stop)
    action_stop_server
    ;;
  *)
    echo "Uso: $0 [install|env|migrate|serve|test|full|stop]"
    exit 1
    ;;
esac
