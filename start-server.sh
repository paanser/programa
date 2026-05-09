#!/usr/bin/env bash

APP_DIR="/home/user/programa"
LOG_FILE="/tmp/php-server.log"
PID_FILE="/tmp/php-server.pid"
PORT=8080

if [ -f "$PID_FILE" ]; then
    OLD_PID=$(cat "$PID_FILE")
    if kill -0 "$OLD_PID" 2>/dev/null; then
        echo "[start-server] Deteniendo servidor anterior (PID $OLD_PID)..."
        kill "$OLD_PID" 2>/dev/null || true
        sleep 1
    fi
    rm -f "$PID_FILE"
fi

if command -v fuser >/dev/null 2>&1; then
    fuser -k "${PORT}/tcp" 2>/dev/null || true
fi

echo "[start-server] Iniciando PHP en 0.0.0.0:${PORT}..."
nohup /usr/bin/php -S "0.0.0.0:${PORT}" -t "$APP_DIR" >> "$LOG_FILE" 2>&1 &
echo $! > "$PID_FILE"
PHP_PID=$!

READY=0
for i in 1 2 3 4 5; do
    sleep 1
    if curl -sf "http://localhost:${PORT}/" -o /dev/null 2>/dev/null; then
        READY=1
        break
    fi
done

if [ "$READY" -eq 1 ]; then
    echo "[start-server] Servidor PHP activo (PID $PHP_PID). Logs: $LOG_FILE"
    exit 0
else
    echo "[start-server] ERROR: El servidor no respondió en 5 segundos." >&2
    tail -5 "$LOG_FILE" >&2
    exit 1
fi
