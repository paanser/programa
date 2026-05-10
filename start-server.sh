#!/usr/bin/env bash

APP_DIR="/home/user/programa"
LOG_FILE="/tmp/php-server.log"
PID_FILE="/tmp/php-server.pid"
WATCHDOG_PID_FILE="/tmp/php-watchdog.pid"
PORT=8080

# ── Función: arrancar PHP ────────────────────────────────────────
start_php() {
    # Liberar el puerto
    if command -v fuser >/dev/null 2>&1; then
        fuser -k "${PORT}/tcp" 2>/dev/null || true
        sleep 0.5
    fi

    echo "[server] $(date '+%Y-%m-%d %H:%M:%S') Arrancando PHP en 0.0.0.0:${PORT}..."
    nohup /usr/bin/php -S "0.0.0.0:${PORT}" -t "$APP_DIR" >> "$LOG_FILE" 2>&1 &
    echo $! > "$PID_FILE"

    # Esperar a que responda
    for i in 1 2 3 4 5; do
        sleep 1
        if curl -sf "http://localhost:${PORT}/" -o /dev/null 2>/dev/null; then
            echo "[server] PHP activo (PID $(cat $PID_FILE))"
            return 0
        fi
    done
    echo "[server] ERROR: PHP no respondió" >&2
    return 1
}

# ── Comprobar si ya está corriendo ───────────────────────────────
php_alive() {
    [ -f "$PID_FILE" ] \
        && kill -0 "$(cat $PID_FILE)" 2>/dev/null \
        && curl -sf "http://localhost:${PORT}/" -o /dev/null 2>/dev/null
}

# ── Arrancar PHP si no está activo ───────────────────────────────
if ! php_alive; then
    [ -f "$PID_FILE" ] && rm -f "$PID_FILE"
    start_php || exit 1
fi

# ── Arrancar watchdog si no está activo ──────────────────────────
watchdog_alive() {
    [ -f "$WATCHDOG_PID_FILE" ] && kill -0 "$(cat $WATCHDOG_PID_FILE)" 2>/dev/null
}

if ! watchdog_alive; then
    (
        while true; do
            sleep 30
            if ! php_alive; then
                echo "[watchdog] $(date '+%Y-%m-%d %H:%M:%S') PHP caído — reiniciando..." >> "$LOG_FILE"
                [ -f "$PID_FILE" ] && rm -f "$PID_FILE"
                start_php >> "$LOG_FILE" 2>&1
            fi
        done
    ) &
    echo $! > "$WATCHDOG_PID_FILE"
    echo "[watchdog] Iniciado (PID $(cat $WATCHDOG_PID_FILE)) — comprueba cada 30s"
fi

exit 0
