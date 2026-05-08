<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/auth.php';

$lang = get_current_lang($_POST);
$redirect = sanitize_redirect_path((string)($_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php'));
$error = null;
$username = trim((string)($_POST['username'] ?? ''));

if (get_current_user_auth() !== null) {
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');

    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, name FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, (string)$user['password_hash'])) {
            login_user($user);
            header('Location: ' . $redirect);
            exit;
        }

        $error = tr('invalid_credentials', $lang);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(tr('login_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1><?= h(tr('login_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <label class="lang-switcher">
            <span><?= h(tr('language', $lang)) ?></span>
            <select onchange="window.location.href=this.value">
                <option value="<?= h(url_with_lang('login.php', ['redirect' => $redirect], 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('login.php', ['redirect' => $redirect], 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
    </div>
</header>

<main class="layout" style="grid-template-columns: 1fr;">
    <section class="panel auth-panel">
        <?php if ($error): ?>
            <div class="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="lang" value="<?= h($lang) ?>">
            <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
            <label><?= h(tr('username', $lang)) ?>
                <input type="text" name="username" value="<?= h($username) ?>" required autocomplete="username">
            </label>
            <label><?= h(tr('password', $lang)) ?>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <div class="actions">
                <button type="submit"><?= h(tr('login', $lang)) ?></button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
