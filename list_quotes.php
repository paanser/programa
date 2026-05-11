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
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= h(tr('history_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1><?= h(tr('history_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="designer.php">Configurador</a>
            <a href="descompuesto.php">Descompuesto S28</a>
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
        <div class="table-wrap quotes-table-wrap">
            <table class="quotes-table">
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
                        <td data-label="ID"><?= (int)$row['id'] ?></td>
                        <td data-label="<?= h(tr('quote', $lang)) ?>"><?= h((string)$row['quote_number']) ?></td>
                        <td data-label="<?= h(tr('date', $lang)) ?>"><?= h((string)$row['created_at']) ?></td>
                        <td data-label="<?= h(tr('client', $lang)) ?>"><?= h((string)$row['client_name']) ?></td>
                        <td data-label="<?= h(tr('system', $lang)) ?>"><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                        <td data-label="<?= h(tr('total', $lang)) ?>"><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
                        <td data-label="<?= h(tr('actions', $lang)) ?>" class="quotes-table__actions">
                            <a class="quotes-table__action-link" href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
                            <a class="quotes-table__action-link" href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
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
