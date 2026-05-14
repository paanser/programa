<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$error = null;
$rows = [];

try {
    $pdo = get_pdo();
    $perPage = 50;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $totalRows = (int)$pdo->query('SELECT COUNT(*) FROM quotes')->fetchColumn();
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT id, quote_number, created_at, client_name, system_type, total FROM quotes ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
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
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>">Nuevo presupuesto</a>
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
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= h(url_with_lang('list_quotes.php', ['page' => $page - 1], $lang)) ?>" class="link-button">&laquo; <?= h(tr('previous', $lang)) ?></a>
            <?php endif; ?>
            <span class="pagination-info"><?= h(tr('page', $lang)) ?> <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= h(url_with_lang('list_quotes.php', ['page' => $page + 1], $lang)) ?>" class="link-button"><?= h(tr('next', $lang)) ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
