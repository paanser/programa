<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$error = null;
$rows = [];

try {
    $pdo = get_pdo();
    $rows = $pdo->query('SELECT id, quote_number, created_at, client_name, system_type, total FROM quotes ORDER BY id DESC LIMIT 200')->fetchAll();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(tr('history_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1><?= h(tr('history_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>" class="active"><?= h(tr('history', $lang)) ?></a>
        </nav>
        <label class="lang-switcher">
            <span><?= h(tr('language', $lang)) ?></span>
            <select onchange="window.location.href=this.value">
                <option value="<?= h(url_with_lang('list_quotes.php', [], 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('list_quotes.php', [], 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
    </div>
</header>

<main class="layout" style="grid-template-columns: 1fr;">
    <section class="panel">
        <?php if ($error): ?>
            <div class="alert">Error: <?= h($error) ?></div>
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= h(tr('quote', $lang)) ?></th>
                    <th><?= h(tr('date', $lang)) ?></th>
                    <th><?= h(tr('client', $lang)) ?></th>
                    <th><?= h(tr('system', $lang)) ?></th>
                    <th><?= h(tr('total', $lang)) ?></th>
                    <th><?= h(tr('actions', $lang)) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= h((string)$row['quote_number']) ?></td>
                        <td><?= h((string)$row['created_at']) ?></td>
                        <td><?= h((string)$row['client_name']) ?></td>
                        <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
                        <td>
                            <a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
                            |
                            <a href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
