<?php

declare(strict_types=1);

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function get_current_user_auth(): ?array
{
    ensure_session_started();
    $user = $_SESSION['auth_user'] ?? null;

    if (!is_array($user) || !isset($user['id'], $user['username'], $user['name'])) {
        return null;
    }

    return [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'name' => (string)$user['name'],
    ];
}

function is_admin_user(?array $user = null): bool
{
    $user = $user ?? get_current_user_auth();

    return is_array($user) && (($user['username'] ?? '') === 'admin');
}

function login_user(array $user): void
{
    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'name' => (string)$user['name'],
    ];
}

function require_auth(string $lang): array
{
    $user = get_current_user_auth();
    if ($user !== null) {
        return $user;
    }

    $redirect = sanitize_redirect_path((string)($_SERVER['REQUEST_URI'] ?? 'index.php'));
    header('Location: ' . url_with_lang('login.php', ['redirect' => $redirect], $lang));
    exit;
}

function logout_user(): void
{
    ensure_session_started();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, (string)$params['path'], (string)$params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }

    session_destroy();
}

function sanitize_redirect_path(string $redirect, string $fallback = 'index.php'): string
{
    $redirect = trim($redirect);
    if ($redirect === '' || str_starts_with($redirect, '//')) {
        return $fallback;
    }

    if (parse_url($redirect, PHP_URL_SCHEME) !== null || parse_url($redirect, PHP_URL_HOST) !== null) {
        return $fallback;
    }

    return $redirect;
}
